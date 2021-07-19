<?php

namespace ChrisGruen\RealtyManager\Import;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use ChrisGruen\RealtyManager\Configuration\ConfigurationImport;
use ChrisGruen\RealtyManager\Import\AttachmentImporter;
use ChrisGruen\RealtyManager\Import\XmlConverter;
use ChrisGruen\RealtyManager\Domain\Model\Objectimmo;
use ChrisGruen\RealtyManager\Domain\Repository\ObjectimmoRepository;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;


class OpenImmoImport
{
    /**
     * @var string
     */
    const FULL_TRANSFER_MODE = 'VOLL';

    /**
     * @var string stores the complete log entry
     */
    private $logEntry = '';

    /**
     * @var string stores the complete error log
     */
    private $errors = '';

    /**
     * @var string Stores log information to be written to "$logEntry". So it
     *             is possible to use only parts of the entire log e.g., to send
     *             emails only about the import of certain records to a certain
     *             contact address.
     */
    private $temporaryLogEntry = '';

    /**
     * @var string Stores log information to be written to $errors. So it is
     *             possible to use only parts of the entire log.
     */
    private $temporaryErrorLog = '';

    /**
     * @var \DOMDocument the current imported XML
     */
    private $importedXml = null;

    /**
     * @var \Tx_Oelib_ConfigurationProxy to access the EM configuration
     */
    private $globalConfiguration = null;

    /**
     * @var \ConfigurationImport for settings
     */
    private $settings = null;

    /**
     * @var Objectimmo | null
     */
    private $realtyObject = null;

    /**
     * @var \tx_realty_translator
     */
    private static $translator = null;

    /**
     * @var bool whether the current zip file should be deleted
     */
    private $deleteCurrentZipFile = true;


    private $filesToDelete = [];

    /**
     * whether the class is tested and only dummy records should be created
     *
     * @var bool
     */
    private $isTestMode = false;

    /**
     * @var bool
     */
    private $success = true;
    
    
    private $objectimmoRepository;
    //private $documentBaseRoot = __DIR__;

    /**
     * Constructor.
     *
     * @param bool $isTestMode
     *        whether the class ist tested and therefore only dummy records should be inserted into the database
     */
    public function __construct($isTestMode = false)
    {
        $this->isTestMode = $isTestMode;
        $this->settings = GeneralUtility::makeInstance(ConfigurationImport::class);
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->objectimmoRepository = $objectManager->get(ObjectimmoRepository::class);
    }

    /**
     * @return bool
     */
    public function wasSuccessful()
    {
        return $this->success;
        //return false;
    }

    /**
     * @param string $importDirectory unified path of the import directory, must not be empty
     *
     * @return bool
     */
    private function canStartImport($importDirectory)
    {
        $isAccessible = true;

        $storageId = (int)$this->settings->getStorageUidImporter();

        $storage = $this->getResourceFactory()->getStorageObject($storageId);
        $pathExists = $storage->hasFolder($importDirectory);

        if (!$pathExists) {
            $this->addToErrorLog(
                \sprintf(LocalizationUtility::translate('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:message_import_directory_not_existing', 'Importing is impossible because the configured import directory &quot;%s&quot; does not exist. Please check your configuration and restart the import.'),$importDirectory)
            );

            $isAccessible = false;
        }
        return $isAccessible;
    }

    /**
     * @return string log entry with information about the proceedings of
     *                ZIP import, will not be empty, contains at least a
     *                timestamp
     */
    public function importFromZip($employer_folder)
    {
        /* root folder fileadmin */
        $import_folder = $this->settings->getResourceFolderImporter().'/'.$employer_folder;

        $this->success = true;

        $this->addToLogEntry(\date('Y-m-d G:i:s') . "\n");

        if (!$this->canStartImport($import_folder)) {
            $this->storeLogsAndClearTemporaryLog();
            return $this->logEntry;
        }

        $zipsToExtract = $this->getPathsOfZipsToExtract($import_folder);

        //$this->storeLogsAndClearTemporaryLog();

        if (empty($zipsToExtract)) {
            $this->addToErrorLog(
                LocalizationUtility::translate('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:message_no_zips', 'No ZIPs to extract. The configured import folder does not contain any ZIP archives. Please check the path configured in the extension manager and the contents of the folder.')
            );
        } else {
            $clear_employer_entries = $this->clearEmployerEntries($employer_folder);
            foreach ($zipsToExtract as $currentZip) {
                $this->extractZip($currentZip);
                $xml_file_data = $this->loadXmlFile($currentZip);
                $recordData = $this->processRealtyRecordInsertion($employer_folder,$currentZip);
            }
        }

        $delImportFolder = $this->deleteImportFolder($employer_folder);
        $clearImageTables = $this->clearImageTables($employer_folder, 'import');
        //$clearZipFile = $this->deleteZipFile($employer_folder);
        //$this->sendEmails($this->prepareEmails($emailData));

        $this->storeLogsAndClearTemporaryLog();

        return $this->logEntry;
    }

    /**
     *
     * @param string $pathOfZip absolute path where to find the ZIP archive which includes an XML file,
     *        must not be empty
     *
     * @return void
     */
    protected function loadXmlFile($pathOfZip)
    {
        $xml_file_data = "";
        $xmlPath = $this->getPathForXml($pathOfZip);

        if ($xmlPath === '') {
            return;
        }

        if (!file_exists($xmlPath)) {
            $this->addToLogEntry(
                LocalizationUtility::translate('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:message_invalid_schema_file_path', 'The path of the schema file is invalid. Please check your configuration in the extension manager.')
            );
        } else {
            $this->importedXml = new \DOMDocument();
            $this->importedXml->load($xmlPath);
        }
    }

    /**
     *
     * @param string $pathOfZip absolute path where to find the ZIP archive which includes an XML file,
     *        must not be empty
     *
     * @return string absolute path of the XML file, empty string on error
     */
    protected function getPathForXml($pathOfZip)
    {
        $result = '';

        $errorMessage = '';
        $folderWithXml = $this->getNameForExtractionFolder($pathOfZip);
       
        if (\is_dir($folderWithXml)) {
            $pathOfXml = \glob($folderWithXml . '*.xml');

            if (\count($pathOfXml) === 1) {
                $result = \implode('', $pathOfXml);
            } elseif (\count($pathOfXml) > 1) {
                $this->addToErrorLog(
                    LocalizationUtility::translate('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:message_too_many_xml', 'There must not be more than one XML file in the ZIP archive. Please remove the surplus XML files from the archive.')
                );
            } else {
                $this->addToErrorLog(
                    LocalizationUtility::translate('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:message_no_xml', 'No XML file could be found in the ZIP archive. If there is one anyway, please ensure the name ends with .xml')
                );
            }
        } else {
            $this->addToErrorLog(
                LocalizationUtility::translate('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:message_invalid_xml_path', 'The path of the XML file is not valid. There might be no extraction folder. This could be a file permissions problem. Please check you are permitted to write in the import directory.')
            );
        }

        return $result;
    }

    /**
     *
     * @param string $zipToExtract path to the ZIP archive to extract, must not be empty
     *
     * @return void
     */
    public function extractZip($zipToExtract)
    {
        if (!file_exists($zipToExtract)) {
            return;
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipToExtract)) {
            $extractionDirectory = $this->checkExtractionFolder($zipToExtract);
            if ($extractionDirectory !== '') {
                if (count(glob($extractionDirectory.'/*')) === 0) {
                    $zip->extractTo($extractionDirectory);
                    $this->addToLogEntry(
                        $zipToExtract . "\n". LocalizationUtility::translate('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:message_extracted_successfully', 'The ZIP archive was extracted successfully'). "\n"
                    );
                }
            }
            $zip->close();
        } else {
            $this->addToErrorLog(
                $zipToExtract . ': ' . LocalizationUtility::translate('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:message_extraction_failed', 'The extraction of this ZIP archive failed. The ZIP archive could not be opened. The file might be corrupt.')
            );
            return;
        }
    }


    /**
     * start process for dataset insert to "tx_realtymanager_domain_model_objectimmo"
     * start importAttachments
     */
    private function processRealtyRecordInsertion($employer_folder, $pathOfCurrentZipFile)
    {
        $emailData = [];
        $offererId = '';
        $transferMode = null;


        $xml = $this->getImportedXml();

        if ($xml instanceof \DOMDocument) {
            $xPath = new \DOMXPath($xml);
            $offererNodes = $xPath->query('//openimmo/anbieter/openimmo_anid');
            $offererNode = $offererNodes->item(0);
            if ($offererNode instanceof \DOMNode) {
                $offererId = (string)$offererNode->nodeValue;
            }
            $ownerNodes = $xPath->query('//openimmo/anbieter/anbieternr');
            $ownerNode = $ownerNodes->item(0);
            if ($ownerNode instanceof \DOMNode) {
                $ownerId = (string)$ownerNode->nodeValue;
            }

            $transferNode = $xPath->query('//openimmo/uebertragung')->item(0);
            if ($transferNode instanceof \DOMNode) {
                $transferMode = $transferNode->getAttribute('umfang');
            }
        }

        if (!$this->hasValidOwnerForImport($employer_folder, $ownerId)) {
            $this->addToErrorLog (
                LocalizationUtility::translate('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:message_openimmo_anid_not_matches_allowed_fe_user', 'The record was not imported because the option to restrict import on registered users is activated and this records OpenImmo ANID did not match any OpenImmo ANID of a FE user who is in the allowed user groups. The records OpenImmo ANID was:') . '"'.
                $ownerId. '".' . "\n"
            );
            return;
        }

        $recordsToInsert = $this->convertDomDocumentToArray($xml);

        if (!empty($recordsToInsert)) {

            foreach ($recordsToInsert as $record) {
                $dataForDatabase = $record;
                unset($dataForDatabase['attached_files']);
                $dataset_to_mysql = $this->writeToDatabase($ownerId, $dataForDatabase);

                /* if dataset save, start process importAttachments */
                if ($dataset_to_mysql == true) {
                    $this->importAttachments($ownerId, $record, $pathOfCurrentZipFile, $employer_folder);
                }
                //$this->storeLogsAndClearTemporaryLog();
            }

            /* last dev point */
            return $this->logEntry;
        }

        return true;
        //return $emailData;
    }

    protected function getImportedXml()
    {
        return $this->importedXml;
    }

    /**
     * Converts a DOMDocument to an array.
     *
     * @param \DOMDocument|null $realtyRecords which contains realty records
     *
     * @return array[] $realtyRecords realty records in an array, will be empty if the data was not convertible
     */
    protected function convertDomDocumentToArray(\DOMDocument $realtyRecords = null)
    {
        if ($realtyRecords === null) {
            return [];
        }

        $domDocumentConverter = new XmlConverter;
        $recordsToInsert =  $domDocumentConverter->getConvertedData($realtyRecords);
        return $recordsToInsert;
    }

    /**
     * @param array $realtyRecord
     *
     * @return boolean
     */
    protected function writeToDatabase($ownerId, array $realtyRecord)
    {
        if ($realtyRecord['object_number'] != '') {
            $setNewObject = $this->objectimmoRepository->setNewObject($ownerId, $realtyRecord);
        }

        $message = '';
        if ($setNewObject == true) {
            $message = "ADD: new object (number): " .$realtyRecord['object_number']." created in table: tx_realtymanager_domain_model_objectimmo";
            $this->addToLogEntry("\n" . $message . "\n");
            return true;
        } else {
            $object_number = $realtyRecord['object_number'] ;
            if($object_number != '') {
                $delete_entry = $this->objectimmoRepository->delObject($ownerId, $realtyRecord);
                $message = "DELETE: Object number ". $realtyRecord['object_number'] ." Dataset removed";
                $this->addToLogEntry("\n" . $message . "\n");
            }
            return false;
        }
    }

    /**
     * @param array $objectData
     *
     * @return void
     */
    private function importAttachments($ownerId, array $objectData, $pathOfCurrentZipFile, $employer_folder)
    {
        if (empty($objectData['attached_files'])) {
            return;
        }

        $attachmentImporter = GeneralUtility::makeInstance(AttachmentImporter::class, $ownerId, $objectData, $this->objectimmoRepository);
        $attachmentImporter->startTransaction();

        $extractionFolder = $this->getFolderForImport($pathOfCurrentZipFile, $employer_folder);

        /** @var string[] $attachmentData */
        foreach ($objectData['attached_files'] as $attachmentData) {
            $fileExtractionPath = $extractionFolder.$attachmentData['path'];
            $title = $attachmentData['title'];
            if($attachmentImporter->addAttachment($fileExtractionPath, $employer_folder, $title) == true) {
                $this->addToLogEntry($fileExtractionPath . " file copied \n");
            }
        }

        $attachmentImporter->finishTransaction();
    }

    /**
     * Gets an array of the paths of all ZIP archives in the import folder
     * and its subfolders.
     *
     * @param string $importDirectory absolute path of the directory which contains the ZIPs, must not be empty
     *
     * @return string[] absolute paths of ZIPs in the import folder, might be empty
     */
    protected function getPathsOfZipsToExtract($importDirectory)
    {
        $base_path = realpath(__DIR__ . '/../../../../../');
        $path_import = $base_path.'/fileadmin'.$importDirectory;
        
        $result = [];

        if (is_dir($path_import)) {
            $result = GeneralUtility::getAllFilesAndFoldersInPath([], $path_import, 'zip');
        }

        return $result;
    }


    /**
     * Gets the full path for the folder according to the ZIP archive to extract to it.
     *
     * @param string $pathOfZip path of a ZIP archive, must not be empty
     *
     * @return string absolute path for a folder named like the ZIP archive
     *
     * @throws \InvalidArgumentException
     */
    protected function getNameForExtractionFolder($pathOfZip) {
        $extractions_folder = str_replace('.zip', '/', $pathOfZip);
        return $extractions_folder;
    }

    protected function getFolderForImport($pathOfZip, $employer_folder) {

        $extractions_folder = '';
        $import_folder = $this->settings->getResourceFolderImporter().'/'.$employer_folder;
        $resource_folder = str_replace('.zip', '/', basename($pathOfZip));

        $extractions_folder = $import_folder . '/'. $resource_folder;
        return $extractions_folder;
    }

    /**
     * Creates a folder to extract a ZIP archive to.
     *
     * @param string $pathOfZip path of a ZIP archive to get the folders name, must not be empty
     *
     * @return string path for folder named like the ZIP archive without
     *                the suffix ".zip", may be empty if the provided ZIP
     *                file does not exists or if the folder to create already exists
     */
    public function checkExtractionFolder($pathOfZip)
    {
        if (!\file_exists($pathOfZip)) {
            return '';
        }

        $folderForZipExtraction = $this->getNameForExtractionFolder($pathOfZip);

        if (\is_dir($folderForZipExtraction)) {            
            $this->addToLogEntry(
                $folderForZipExtraction . "\n" . LocalizationUtility::translate('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:message_import_folder_true', 'The folder for import files was created'). "\n"
            );
        } else {
            $create_import_folder = GeneralUtility::mkdir_deep($folderForZipExtraction);
            
            if (\is_dir($folderForZipExtraction)) { 
                $this->filesToDelete[] = $folderForZipExtraction;
                
                $this->addToLogEntry(
                    $folderForZipExtraction . "\n" . LocalizationUtility::translate('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:message_import_folder_true', 'The folder for import files was created'). "\n"
                );
            } else {
                $this->addToLogEntry(
                    $folderForZipExtraction . "\n" . LocalizationUtility::translate('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:message_import_folder_false', 'The folder for import files not exist'). "\n"
                );
                return '';
            }
        }
        return $folderForZipExtraction;
    }

    protected function clearEmployerEntries($employer_folder) {

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_realtymanager_domain_model_employer');

        $sql = "SELECT pid_be_user 
                FROM tx_realtymanager_domain_model_employer 
                WHERE import_folder = '".$employer_folder."'";
        
        $employer_pid = $connection->executeQuery($sql)->fetch();
        $employer_pid = $employer_pid['pid_be_user'];

        $clearFolderEmployerEntries = $this->deleteFilesFolder($employer_folder, 'export');

        if($clearFolderEmployerEntries == true) {
            $this->addToLogEntry(
                "\n Clear file folder realty/".$employer_folder. " \n"
            );
        }

        if($clearFolderEmployerEntries == true) {
            $clearTableEmployerEntries = $this->deleteTableEntries($employer_pid, $employer_folder);
        }
    }

    /**
     * clear folder
     */
    protected function deleteFilesFolder($employer_folder, $type)
    {
        $base_path = realpath(__DIR__ . '/../../../../../');
        if($type == 'export') {
            $realty_folder = $base_path.'/fileadmin'.$this->settings->getResourceFolderExporter().'/'.$employer_folder;
        }
        if($type == 'import') {
            $realty_folder = $base_path.'/fileadmin'.$this->settings->getResourceFolderImporter().'/'.$employer_folder;
        }

        if (is_dir($realty_folder)) {
            $files = glob($realty_folder.'/*');

            foreach($files as $file) {
                if(is_file($file))
                    unlink($file);
            }
            return true;
        }
    }

    /**
     * clear TablesEntries from Employer
     */
    protected function deleteTableEntries($employer_pid, $employer_folder)
    {
        $clearImageTables = $this->clearImageTables($employer_folder, 'export');

        if ($clearImageTables == true) {
            $delete_all_entries = $this->objectimmoRepository->delAllObjects($employer_pid);
        }
    }

    /**
     * delete Importfolder
     */
    protected function deleteImportFolder($employer_folder)
    {
        $import_folder = $this->settings->getResourceFolderImporter().'/'.$employer_folder;
        $storageId = (int)$this->settings->getStorageUidImporter();
        $storage = $this->getResourceFactory()->getStorageObject($storageId);
        $pathExists = $storage->hasFolder($import_folder);

        if ($pathExists) {
            $base_path = realpath(__DIR__ . '/../../../../../');
            $import_folder = $base_path.'/fileadmin'.$this->settings->getResourceFolderImporter().'/'.$employer_folder;
            $subdirectories = self::getSubDirectories($import_folder);
            foreach ($subdirectories as $key => $subdirectory) {
                if ($key == 0)
                    continue;

                self::delTree($subdirectory);
                $this->addToLogEntry(
                    "\n DELETE Import folder: ".$employer_folder."/".$subdirectory
                );
            }
            $this->addToLogEntry(
                "\n Clear Importfolder \n"
            );
        } else {
            $this->addToLogEntry(
                "\n Importfolder not exist! \n"
            );
        }
    }

    /**
     * clear Importfolder (files)
     */
    protected function deleteZipFile($employer_folder)
    {
        $base_path = realpath(__DIR__ . '/../../../../../');
        $files_employer_folder = glob($base_path . '/fileadmin' . $this->settings->getResourceFolderImporter() . '/' . $employer_folder . '/*');
        
        if (count($files_employer_folder) > 0) {
            foreach ($files_employer_folder as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            $this->addToErrorLog(
                "\n Remove Files from employer folder \n"
            );
        } else {
            $this->addToErrorLog(
                "\n No (zip)-files detected \n"
            );
        }
    }

    public static function getSubDirectories($dir)
    {
        $subDir = array();
        $directories = array_filter(glob($dir), 'is_dir');
        $subDir = array_merge($subDir, $directories);
        foreach ($directories as $directory) $subDir = array_merge($subDir, self::getSubDirectories($directory.'/*'));
        return $subDir;
    }


    public static function delTree($dir)
    {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? self::delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    protected function clearImageTables($dir, $type)
    {
        if($type == 'export') {
            $identifier_phrase = 'realty/'.$dir;
        } else {
            $identifier_phrase = 'import/'.$dir;
        }
                
        $delattachements = $this->objectimmoRepository->clearSysFiles($identifier_phrase);

        if(clearSysFiles == true) {
            $this->addToLogEntry(
                "\n Clear image table: ".$identifier_phrase. " \n"
            );
            return true;
        }
        return false;
    }


    private function hasValidOwnerForImport($employer_folder, $ownerId)
    {
        $checkOwnerAnid = $this->objectimmoRepository->checkOwnerAnid($employer_folder, $ownerId);
        return $checkOwnerAnid;
    }


    /**
     * @return ResourceFactory
     */
    protected function getResourceFactory(): ResourceFactory
    {
        return GeneralUtility::makeInstance(ResourceFactory::class);
    }
    
    /**
     * Logs information about the proceedings of the import.
     * This function is to be used for positive information only. Errors should
     * get logged through 'addToErrorLog()' instead.
     *
     * @param string $message message to log, may be empty
     *
     * @return void
     */
    private function addToLogEntry($message)
    {
        print_r($message); // for cronjob show messages
        $this->temporaryLogEntry .= $message . "\n";
    }
    
    /**
     * Logs errors to a special error log and also provides 'addToLogEntry()'
     * with the given string.
     *
     * @param string $errorMessage error message to log, may be empty
     *
     * @return void
     */
    private function addToErrorLog($errorMessage)
    {
        $this->success = false;
        $this->temporaryErrorLog .= $errorMessage . "\n";
        $this->addToLogEntry($errorMessage);
    }
    
    /**
     * Stores available log messages to be returned at the end of the import.
     * This function is needed to use only parts of the log.
     *
     * @return void
     */
    private function storeLogsAndClearTemporaryLog()
    {
        $this->errors .= $this->temporaryErrorLog;
        $this->temporaryErrorLog = '';
        
        $this->logEntry .= $this->temporaryLogEntry;
        $this->temporaryLogEntry = '';
    }
}
