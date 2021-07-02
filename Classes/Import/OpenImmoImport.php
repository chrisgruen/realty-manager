<?php

namespace ChrisGruen\RealtyManager\Import;

use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use ChrisGruen\RealtyManager\Configuration\ConfigurationImport;
use ChrisGruen\RealtyManager\Import\AttachmentImporter;
use ChrisGruen\RealtyManager\Import\XmlConverter;
use ChrisGruen\RealtyManager\Domain\Model\Objectimmo;
use ChrisGruen\RealtyManager\Domain\Repository\ObjectimmoRepository;
use TYPO3\CMS\Core\Localization\LanguageService;
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
    }
    
    private $objectimmoRepository;
    
    /**
     * Inject the objectimmo repository
     *
     * @param ChrisGruen\RealtyManager\Domain\Repository\ObjectimmoRepository $objectimmoRepository
     */
    public function injectObjectimmoRepository(ObjectimmoRepository $objectimmoRepository)
    {
        $this->objectimmoRepository = $objectimmoRepository;
    }
    
    /**
     * @return bool
     */
    public function wasSuccessful()
    {
        return $this->success;
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
        $languageServiceBackup = $this->getLanguageService();
              
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
                \sprintf($this->getLanguageService()->sL('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:' . 'message_no_zips'),$import_path)
            ); 
        } else {
            
            foreach ($zipsToExtract as $currentZip) {
                $this->extractZip($currentZip);  
                $xml_file_data = $this->loadXmlFile($currentZip);
                $recordData = $this->processRealtyRecordInsertion($employer_folder,$currentZip);
                    //$emailData = \array_merge($emailData, $recordData);
                }
        }
            
        //$this->sendEmails($this->prepareEmails($emailData));
        //$this->cleanUp($checkedImportDirectory);

        $this->storeLogsAndClearTemporaryLog();

        return $this->logEntry;
    }

    /**
     * Returns the LanguageService
     *
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
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
                $this->getLanguageService()->sL('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:' . 'message_openimmo_anid_not_matches_allowed_fe_user') . '"'.
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
                
                /* last dev point */
                return $this->logEntry;
    
                //$this->storeLogsAndClearTemporaryLog();
            }
    
            if ($transferMode === self::FULL_TRANSFER_MODE
                && $this->globalConfiguration->getAsBoolean('importCanDeleteRecordsForFullSync')
            ) {
                $pid = \Tx_Oelib_ConfigurationProxy::getInstance('realty')->getAsInteger('pidForRealtyObjectsAndImages');
                /** @var \tx_realty_Mapper_RealtyObject $realtyObjectMapper */
                $realtyObjectMapper = \Tx_Oelib_MapperRegistry::get(\tx_realty_Mapper_RealtyObject::class);
                $deletedObjects = $realtyObjectMapper->deleteByAnidAndPidWithExceptions(
                    $offererId,
                    $pid,
                    $savedRealtyObjects
                );
                if (!empty($deletedObjects)) {
                    /** @var string[] $uids */
                    $uids = [];
                    foreach ($deletedObjects as $deletedObject) {
                        $uids[] = $deletedObject->getUid();
                    }
                    $this->addToLogEntry(
                        $this->getLanguageService()->sL('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:' . 'message_deleted_objects_from_full_sync') . ' ' .
                        \implode(', ', $uids)
                    );
                }
            }
    
            if (!$this->deleteCurrentZipFile) {
                $this->filesToDelete = \array_diff($this->filesToDelete, [$pathOfCurrentZipFile]);
                $this->deleteCurrentZipFile = true;
            }
        }
        return true;
        //return $emailData;
    }
    
    /**
     * @param array $realtyRecord
     *
     * @return boolean
     */
    protected function writeToDatabase($ownerId, array $realtyRecord)
    {
        $setNewObject = $this->objectimmoRepository->setNewObject($ownerId, $realtyRecord);
        
        $message = '';
        if ($setNewObject == true) {
            $message = "new entry in table tx_realtymanager_domain_model_objectimmo";
            $this->addToLogEntry($message . "\n");
            return true;
        } else {
            $message = "Error: dataset can not save";
            $this->addToLogEntry($message . "\n");
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
            $attachmentImporter->addAttachment($fileExtractionPath, $employer_folder, $title);
        }
        
        
        exit();

        $attachmentImporter->finishTransaction();
    }



    /**
     * Returns a localized message that validation should be activated. Will be
     * empty if validation is active.
     *
     * @return string localized message that validation should be enabled,
     *                an empty string if this is already enabled
     */
    private function getPleaseActivateValidationMessage()
    {
        return ($this->globalConfiguration->getAsString('openImmoSchema') !== '')
            ? $this->getLanguageService()->sL('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:' . 'message_please_validate')
            : '';
    }


    private function hasValidOwnerForImport($employer_folder, $ownerId)
    {
        $checkOwnerAnid = $this->objectimmoRepository->checkOwnerAnid($employer_folder, $ownerId);
        return $checkOwnerAnid;
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
                \sprintf($this->getLanguageService()->sL('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:' . 'message_import_directory_not_existing'),$importDirectory)
            );
            
            $isAccessible = false;
        }
        
        return $isAccessible;
    }

    /**
     * Checks if the configuration in the EM enables sending errors only.
     *
     * @return bool whether "onlyErrors" is enabled
     */
    private function isErrorLogOnlyEnabled()
    {
        return $this->globalConfiguration->getAsBoolean('onlyErrors');
    }

    /**
     * Returns the default email address, configured in the EM.
     *
     * @return string default email address, may be empty
     */
    private function getDefaultEmailAddress()
    {
        return $this->globalConfiguration->getAsString('emailAddress');
    }

    /**
     * Checks whether contact persons of each record should receive emails
     * about the import of their records.
     *
     * @return bool whether contact persons should receive emails
     */
    private function isNotifyContactPersonsEnabled()
    {
        return $this->globalConfiguration->getAsBoolean('notifyContactPersons');
    }


    /**
     * Deletes object numbers from $emailData if there is no message to report.
     * Messages could only be empty if 'onlyErrors' is activated in the EM
     * configuration.
     *
     * @param array[] &$emailData prepared email data, must not be empty
     *
     * @return void
     */
    private function purgeRecordsWithoutLogMessages(array &$emailData)
    {
        foreach ($emailData as $recipient => $data) {
            foreach ($data as $key => $emailContent) {
                if (\implode('', $emailContent) === '') {
                    unset($emailData[$recipient][$key]);
                }
            }
        }
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
        $base_path = $_SERVER['DOCUMENT_ROOT'];
        $path_import = $base_path.'/fileadmin'.$importDirectory;
        
        $result = [];
        
        if (is_dir($path_import)) {
            $result = GeneralUtility::getAllFilesAndFoldersInPath([], $path_import, 'zip');
        }

        return $result;
    }

    /**
     * Extracts each ZIP archive into a directory in the import folder which is
     * named like the ZIP archive without the suffix '.zip'.
     * Logs success and failures.
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
            $extractionDirectory = $this->createExtractionFolder($zipToExtract);
            if ($extractionDirectory !== '') {
                if (count(glob($extractionDirectory.'/*')) === 0) {
                    $zip->extractTo($extractionDirectory);
                    $this->addToLogEntry(
                        $zipToExtract . ': '. LocalizationUtility::translate('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:message_extracted_successfully', 'The ZIP archive was extracted successfully.')
                     );
                }               
            }
            $zip->close();
        } else {
            $this->addToErrorLog(
                $zipToExtract . ': ' . LocalizationUtility::translate('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:message_extraction_failed', 'The extraction of this ZIP archive failed. The ZIP archive could not be opened. The file might be corrupt.')
            );
        }
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
    public function createExtractionFolder($pathOfZip)
    {
        if (!\file_exists($pathOfZip)) {
            return '';
        }

        $folderForZipExtraction = $this->getNameForExtractionFolder($pathOfZip);
        
        if (\is_dir($folderForZipExtraction)) {
            $this->addToErrorLog(                
                $folderForZipExtraction . ': ' . LocalizationUtility::translate('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:message_surplus_folder', 'The folder which needs to be created for importing already exists in your import directory. The record cannot be imported as long as this folder exists. Please remove this folder.')
            );
        } else {
            try {
                GeneralUtility::mkdir_deep($folderForZipExtraction);
                $this->filesToDelete[] = $folderForZipExtraction;
                if (!\is_writable($folderForZipExtraction)) {
                    $this->addToErrorLog(
                        \sprintf(
                            $this->getLanguageService()->sL('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:' . 'message_folder_not_writable'),
                            $folderForZipExtraction
                        )
                    );
                }
            } catch (\RuntimeException $exception) {
                $this->addToErrorLog(
                    \sprintf(
                        $this->getLanguageService()->sL('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:' . 'message_folder_creation_failed'),
                        $folderForZipExtraction
                    )
                );
            }
        }

        return $folderForZipExtraction;
    }

    /**
     * Finds an XML file in the folder named like $pathOfZip without the suffix
     * '.zip' and returns its path. The ZIP archive must have been extracted
     * before. In case no or several XML files are found, an empty string is
     * returned and the error is logged.
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
                $errorMessage = 'message_too_many_xml';
            } else {
                $errorMessage = 'message_no_xml';
            }
        } else {
            $errorMessage = 'message_invalid_xml_path';
        }

        if ($errorMessage !== '') {
            $this->addToErrorLog(\basename($pathOfZip) . ': ' . $this->getLanguageService()->sL('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:' . $errorMessage));
        }

        return $result;
    }

    /**
     * Loads and validates an XML file from a ZIP archive as a DOMDocument which
     * is stored in an array.
     * The ZIP archive must have been extracted to a folder named like the ZIP
     * without the suffix '.zip' before.
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
            $this->addToLogEntry($this->getLanguageService()->sL('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:' . 'message_invalid_schema_file_path'));
        } else {
            $this->importedXml = new \DOMDocument();
            $this->importedXml->load($xmlPath);
        }
   }
   
   protected function getImportedXml()
   {
       return $this->importedXml;
   }


    /**
     * Removes the ZIP archives which have been imported and the folders which
     * have been created to extract the ZIP archives.
     * Logs which ZIP archives have been deleted.
     *
     * @param string $importDirectory absolute path of the folder which contains the ZIP archives, must not be empty
     *
     * @return void
     */
    public function cleanUp($importDirectory)
    {
        if (!\is_dir($importDirectory)) {
            return;
        }

        $removedFiles = [];
        $deleteImportedZips = $this->globalConfiguration->getAsBoolean('deleteZipsAfterImport');

        foreach ($this->getPathsOfZipsToExtract($importDirectory) as $currentPath) {
            if ($deleteImportedZips) {
                $removedZipArchive = $this->deleteFile($currentPath);
                if ($removedZipArchive !== '') {
                    $removedFiles[] = $removedZipArchive;
                }
            }
            $this->deleteFile($this->getNameForExtractionFolder($currentPath));
        }

        if (!empty($removedFiles)) {
            $this->addToLogEntry(
                $this->getLanguageService()->sL('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:' . 'message_files_removed') . ': ' . \implode(', ', $removedFiles)
            );
        }
    }

    /**
     * Removes a file if it occurs in the list of files for which deletion is
     * allowed.
     *
     * @param string $pathOfFile path of the file to delete, must not be empty
     *
     * @return string basename of the deleted file or an empty string if
     *                no file was deleted
     */
    private function deleteFile($pathOfFile)
    {
        $removedFile = '';
        if (\in_array($pathOfFile, $this->filesToDelete, true)) {
            GeneralUtility::rmdir($pathOfFile, true);
            $removedFile = \basename($pathOfFile);
        }

        return $removedFile;
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
     * Loads a realty object.
     *
     * The data can either be a database result row or an array which has
     * database column names as keys (may be empty). The data can also be a UID
     * of an existent realty object to load from the database. If the data is of
     * an invalid type the realty object stays empty.
     *
     * @param array|mixed $data
     *        data for the realty object as an array
     *
     * @return void
     */
    protected function loadRealtyObject($data)
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_realtymanager_domain_model_employer');
        $sql = "SHOW COLUMNS FROM tx_realtymanager_domain_model_objectimmo";
        $col_data = $connection->executeQuery($sql)->fetch;
        print_r($col_data);
        exit();
        
        /*
        $this->realtyObject = GeneralUtility::makeInstance(Objectimmo::class, $this->isTestMode);
        $this->realtyObject->loadRealtyObject($data, true);
        */
    }

    /**
     * Returns the object number of a realty object if it is set.
     *
     * @return string object number, may be empty if no object number
     *                was set or if the realty object is not initialized
     */
    private function getObjectNumberFromRealtyObject()
    {
        if (!$this->realtyObject instanceof Objectimmo || $this->realtyObject->isDead()) {
            return '';
        }

        return $this->realtyObject->getProperty('object_number');
    }


    /**
     * Checks whether the owner's data may be used.
     *
     * @return bool whether it is allowed by configuration to use the owner's data
     */
    private function mayUseOwnerData()
    {
        return $this->globalConfiguration->getAsBoolean('useFrontEndUserDataAsContactDataForImportedRecords');
    }


    /**
     * Gets the required fields of a realty object.
     * This function is needed for unit testing only.
     *
     * @return string[] required fields, may be empty if no fields are
     *               required or if the realty object is not initialized
     */
    protected function getRequiredFields()
    {
        if (!$this->realtyObject instanceof Objectimmo) {
            return [];
        }

        return $this->realtyObject->getRequiredFields();
    }
    
    /**
     * @return ResourceFactory
     */
    protected function getResourceFactory(): ResourceFactory
    {
        return GeneralUtility::makeInstance(ResourceFactory::class);
    }
}

