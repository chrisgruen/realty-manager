<?php

namespace ChrisGruen\RealtyManager\Import;

use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use ChrisGruen\RealtyManager\Configuration\ConfigurationImport;
use ChrisGruen\RealtyManager\Import\AttachmentImporter;
use ChrisGruen\RealtyManager\Import\XmlConverter;
use ChrisGruen\RealtyManager\Domain\Model\Objectimmo;
use TYPO3\CMS\Core\Localization\LanguageService;


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

    /**
     * @var string[] ZIP archives which are deleted at the end of import and
     *            folders which were created during the import.
     *            Archives are added to this array if they contain exactly one
     *            XML file as this is the criterion for trying to import the
     *            XML file as an OpenImmo record.
     */
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

    /**
     * Extracts ZIP archives from an absolute path of a directory and inserts
     * realty records to database:
     * If the directory, specified in the EM configuration, exists and ZIP
     * archives are found, folders are created. They are named like the ZIP
     * archives without the suffix '.zip'. The ZIP archives are unpacked to
     * these folders. Then for each ZIP file the following is done: The validity
     * of the XML file found in the ZIP archive is checked by using the XSD
     * file defined in the EM. The realty records are fetched and inserted to
     * database. The validation failures are logged.
     * If the records of one XML could be inserted to database, images found
     * in the extracted ZIP archive are copied to the uploads folder.
     * Afterwards the extraction folders are removed and a log string about the
     * proceedings of import is passed back.
     * Depending on the configuration in EM the log or only the errors are sent
     * via email to the contact addresses of each realty record if they are
     * available. Else the information goes to the address configured in EM. If
     * no email address is configured, the sending of emails is disabled.
     *
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
                $recordData = $this->processRealtyRecordInsertion($currentZip);
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
     * @return bool
     */
    public function wasSuccessful()
    {
        return $this->success;
    }


    private function processRealtyRecordInsertion($pathOfCurrentZipFile)
    {
        $emailData = [];
        //$savedRealtyObjects = new \Tx_Oelib_List();
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

            $transferNode = $xPath->query('//openimmo/uebertragung')->item(0);
            if ($transferNode instanceof \DOMNode) {
                $transferMode = $transferNode->getAttribute('umfang');
            }
        }
        
        if (!$this->hasValidOwnerForImport()) {
            $this->addToErrorLog (
                $this->getLanguageService()->sL('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:' . 'message_openimmo_anid_not_matches_allowed_fe_user') . ' "');
            return;
        }
   
        $recordsToInsert = $this->convertDomDocumentToArray($xml);
        
        if (!empty($recordsToInsert)) {
            echo "recordsToInsert: <br />";
            echo "<pre>";
            print_r($recordsToInsert[0]);
            echo "<pre>";
            exit();

            foreach ($recordsToInsert as $record) {
                $dataForDatabase = $record;
                unset($dataForDatabase['attached_files']);
                $this->writeToDatabase($dataForDatabase);
    
                $realtyObject = $this->realtyObject;
                if (!$realtyObject->isDead() && !$realtyObject->isDeleted()) {
                    $this->importAttachments($record, $pathOfCurrentZipFile);
                    $savedRealtyObjects->add($realtyObject);
                    /*
                    $emailData[] = $this->createEmailRawDataArray(
                        $this->getContactEmailFromRealtyObject(),
                        $this->getObjectNumberFromRealtyObject()
                    );
                    */
                }
                $this->storeLogsAndClearTemporaryLog();
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
     * @param array $objectData
     * @param string $pathOfCurrentZipFile
     *
     * @return void
     */
    private function importAttachments(array $objectData, $pathOfCurrentZipFile)
    {
        if (empty($objectData['attached_files'])) {
            return;
        }

        $attachmentImporter = GeneralUtility::makeInstance(AttachmentImporter::class, $this->realtyObject);
        $attachmentImporter->startTransaction();
        $extractionFolder = $this->getNameForExtractionFolder($pathOfCurrentZipFile);

        /** @var string[] $attachmentData */
        foreach ($objectData['attached_files'] as $attachmentData) {
            $relativePath = $attachmentData['path'];
            $fullPath = $extractionFolder . $relativePath;
            $title = $attachmentData['title'];
            $attachmentImporter->addAttachment($fullPath, $title);
        }

        $attachmentImporter->finishTransaction();
    }

    protected function writeToDatabase(array $realtyRecord)
    {
        $this->loadRealtyObject($realtyRecord);
        $this->ensureContactEmail();

        if (!$this->hasValidOwnerForImport()) {
            $this->addToErrorLog(
                $this->getLanguageService()->sL('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:' . 'message_openimmo_anid_not_matches_allowed_fe_user') . ' "' .
                $this->realtyObject->getProperty('openimmo_anid') . '".' . "\n"
            );
            return;
        }

        // 'TRUE' allows to add an owner to the realty record if it hasn't got one.
        $errorMessage = $this->realtyObject->writeToDatabase(0, true);

        switch ($errorMessage) {
            case '':
                $this->addToLogEntry($this->getLanguageService()->sL('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:' . 'message_written_to_database') . "\n");
                break;
            case 'message_deleted_flag_set':
                // The fall-through is intended.
            case 'message_deleted_flag_causes_deletion':
                // A set deleted flag is no real error, so is not stored in the error log.
                $this->addToLogEntry($this->getLanguageService()->sL('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:' . $errorMessage) . "\n");
                break;
            case 'message_fields_required':
                $this->addToErrorLog(
                $this->getLanguageService()->sL('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:' . $errorMessage) . ': ' .
                    \implode(', ', $this->realtyObject->checkForRequiredFields()) . '. ' .
                    $this->getPleaseActivateValidationMessage() . "\n"
                );
                break;
            case 'message_object_limit_reached':
                $this->deleteCurrentZipFile = false;
                $owner = $this->realtyObject->getOwner();
                $this->addToErrorLog(
                    \sprintf(
                        $this->getLanguageService()->sL('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:' . $errorMessage),
                        $owner->getName(),
                        $owner->getUid(),
                        $owner->getTotalNumberOfAllowedObjects()
                    ) . "\n"
                );
                break;
            default:
                $this->addToErrorLog(
                $this->getLanguageService()->sL('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:' . $errorMessage). ' ' .
                    $this->getPleaseActivateValidationMessage() . "\n"
                );
        }
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


    private function hasValidOwnerForImport()
    {
        return true;
        //return false;
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
     * Checks whether the import may start. Will return true if he import directory is writable.
     * Otherwise, the result will be false and the reason will be logged.
     *
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
     * Adds the given error message to the error log.
     *
     * @param string $message locallang label for the error message to add to the log, must not be empty
     * @param string $path the path to be displayed in the error message, must not be empty
     *
     * @return void
     */
    private function addFolderAccessErrorMessage($message, $path)
    {
        $ownerUid = \fileowner($path);
        if (\function_exists('posix_getpwuid')) {
            $ownerName = \posix_getpwuid($ownerUid) . ', ' . $ownerUid;
        } else {
            $ownerName = $ownerUid;
        }

        $this->addToErrorLog(
            \sprintf(
                $message,
                $path,
                $ownerName,
                \substr(\decoct(\fileperms($path)), 2),
                \get_current_user()
            )
        );
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
     * Stores all information for an email to an array with the keys
     * "recipient", "objectNumber", "logEntry" and "errorLog".
     *
     * @param string $email email address, may be empty
     * @param string $objectNumber object number, may be empty
     *
     * @return string[] email raw data, contains the elements "recipient", "objectNumber", "logEntry" and "errorLog",
     *                  will not be empty
     */
    private function createEmailRawDataArray($email, $objectNumber)
    {
        return [
            'recipient' => $email,
            'objectNumber' => $objectNumber,
            'logEntry' => $this->temporaryLogEntry,
            'errorLog' => $this->temporaryErrorLog,
        ];
    }

    /**
     * Prepares the sending of emails. Resorts $emailData. Sets the value for
     * 'recipient' to the default email address wherever there is no email
     * address given. Sets the value for "objectNumber" to "------" if is not
     * set. Purges empty records, so no empty messages are sent.
     * If "onlyErrors" is enabled in EM, the messages will just contain error
     * messages and no information about success.
     *
     * @param array[] $emailData
     *        Two-dimensional array of email data. Each inner array has the elements "recipient", "objectNumber",
     *        "logEntry" and "errorLog". May be empty.
     *
     * @return array[] Three -dimensional array with email addresses as
     *               keys of the outer array. Innermost there is an array
     *               with only one element: Object number as key and the
     *               corresponding log information as value. This array
     *               is wrapped by a numeric array as object numbers are
     *               not necessarily unique. Empty if the input array is
     *               empty or invalid.
     */
    protected function prepareEmails(array $emailData)
    {
        if (!$this->validateEmailDataArray($emailData)) {
            return [];
        }

        $result = [];
        $emailDataToPrepare = $emailData;
        if ($this->isErrorLogOnlyEnabled()) {
            $log = 'errorLog';
        } else {
            $log = 'logEntry';
        }

        foreach ($emailDataToPrepare as $record) {
            if ((string)($record['recipient'] === '') || !$this->isNotifyContactPersonsEnabled()) {
                $record['recipient'] = $this->getDefaultEmailAddress();
            }

            if ((string)$record['objectNumber'] === '') {
                $record['objectNumber'] = '------';
            }

            $result[$record['recipient']][] = [
                $record['objectNumber'] => $record[$log],
            ];
        }

        $this->purgeRecordsWithoutLogMessages($result);
        $this->purgeRecipientsForEmptyMessages($result);

        return $result;
    }

    /**
     * Validates an email data array which is used to prepare emails. Returns
     * true if the structure is correct, false otherwise.
     *
     * The structure is correct if there are arrays as values for each numeric
     * key and if those arrays contain the elements "recipient", "objectNumber",
     * "logEntry" and "errorLog" as keys.
     *
     * @param array[] $emailData
     *        email data array to validate with arrays as values for each numeric key and if those arrays contain the
     *        elements "recipient", "objectNumber", "logEntry" and "errorLog" as keys, may be empty
     *
     * @return bool whether the structure of the array is valid
     */
    private function validateEmailDataArray(array $emailData)
    {
        $isValidDataArray = true;
        $requiredKeys = [
            'recipient',
            'objectNumber',
            'logEntry',
            'errorLog',
        ];

        foreach ($emailData as $dataArray) {
            if (!\is_array($dataArray)) {
                $isValidDataArray = false;
                break;
            }
            $numberOfValidArrays = \count(\array_intersect(\array_keys($dataArray), $requiredKeys));

            if ($numberOfValidArrays !== 4) {
                $isValidDataArray = false;
                break;
            }
        }

        return $isValidDataArray;
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
     * Deletes email recipients from a $emailData if are no records to report
     * about.
     *
     * @param array[] &$emailData prepared email data, must not be empty
     *
     * @return void
     */
    private function purgeRecipientsForEmptyMessages(array &$emailData)
    {
        foreach ($emailData as $recipient => $data) {
            if (empty($data)) {
                unset($emailData[$recipient]);
            }
        }
    }

    /**
     * Fills a template file, which has already been included, with data for one
     * email.
     *
     * @param array[] $recordsForOneEmail
     *        Wrapped message content for one email: Each object number-message pair is wrapped by a numeric key as
     *     object numbers are not necessarily unique. Must not be empty.
     *
     * @return string email body
     */
    private function fillEmailTemplate($recordsForOneEmail)
    {
        /** @var $template \Tx_Oelib_TemplateHelper */
        $template = GeneralUtility::makeInstance(\Tx_Oelib_TemplateHelper::class);
        $template->init(['templateFile' => $this->globalConfiguration->getAsString('emailTemplate')]);
        $template->getTemplateCode();
        $contentItem = [];

        // collects data for the subpart 'CONTENT_ITEM'
        $template->setMarker('label_object_number', $this->getLanguageService()->sL('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:' . 'label_object_number'));
        foreach ($recordsForOneEmail as $record) {
            // $record is an array of the object number associated with the log
            $template->setMarker('object_number', \key($record));
            $template->setMarker('log', \implode('', $record));
            $contentItem[] = $template->getSubpart('CONTENT_ITEM');
        }

        // fills the subpart 'EMAIL_BODY'
        $template->setMarker('header', $this->getLanguageService()->sL('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:' . 'message_introduction'));
        $template->setSubpart('CONTENT_ITEM', \implode("\n", $contentItem));
        $template->setMarker('footer', $this->getLanguageService()->sL('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:' . 'message_explanation'));

        return $template->getSubpart('EMAIL_BODY');
    }

    /**
     * Sends an email with log information to each address given as a key of
     * $addressesAndMessages.
     * If there is no default address configured in the EM, no messages will be
     * sent at all.
     *
     * @param array[] $addressesAndMessages
     *        Three-dimensional array with email addresses as keys of the outer array. Innermost there is an array
     *     with only one element: Object number as key and the corresponding log information as value. This array is
     *     wrapped by a numeric array as object numbers are not necessarily unique. Must not be empty.
     *
     * @return void
     */
    private function sendEmails(array $addressesAndMessages)
    {
        /** @var SystemEmailFromBuilder $emailRoleBuilder */
        $emailRoleBuilder = GeneralUtility::makeInstance(SystemEmailFromBuilder::class);
        if ($this->getDefaultEmailAddress() === '' || !$emailRoleBuilder->canBuild()) {
            return;
        }

        $fromRole = $emailRoleBuilder->build();
        foreach ($addressesAndMessages as $address => $content) {
            /** @var MailMessage $email */
            $email = GeneralUtility::makeInstance(MailMessage::class);
            $email->setFrom([$fromRole->getEmailAddress() => $fromRole->getName()]);
            $email->setTo([$address => '']);
            $email->setSubject($this->getLanguageService()->sL('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:' . 'label_subject_openImmo_import'));
            $email->setBody($this->fillEmailTemplate($content));
            $email->send();
        }

        if (!empty($addressesAndMessages)) {
            $this->addToLogEntry(
                $this->getLanguageService()->sL('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:' . 'message_log_sent_to') . ': ' . \implode(', ', \array_keys($addressesAndMessages))
            );
        }
    }

    /**
     * Ensures a contact email address for the current realty record. Checks
     * whether there is a valid contact email for the current record. Inserts
     * the default address configured in EM if 'contact_email' if the current
     * record's contact email is empty or invalid.
     *
     * @return void
     */
    protected function ensureContactEmail()
    {
        $address = $this->getContactEmailFromRealtyObject();
        $isValid = ($address !== '') && GeneralUtility::validEmail($address);

        if (!$isValid) {
            $this->setContactEmailOfRealtyObject($this->getDefaultEmailAddress());
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
                    $this->addToLogEntry($zipToExtract . ': ' .  $this->getLanguageService()->sL('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:' . 'message_extracted_successfully'));
                }               
            }
            $zip->close();
        } else {
            $this->addToErrorLog($zipToExtract . ': ' . $this->getLanguageService()->sL('LLL:EXT:realty_manager/Resources/Private/Language/locallang_import.xlf:' . 'message_extraction_failed'));
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
                $folderForZipExtraction . ': ' .  $this->getLanguageService()->sL('LLL:EXT:realty_manager/Resources/Private/Language/locallang_be.xlf:' . 'message_surplus_folder')
            );
            //$folderForZipExtraction = '';
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
     * Returns the contact email address of a realty object.
     *
     * The returned email address depends on the configuration for
     * 'useFrontEndUserDataAsContactDataForImportedRecords'. If this option is
     * enabled and if there is an owner, the owner's email address will be
     * fetched. Otherwise the contact email address found in the realty record
     * will be returned.
     *
     * @return string email address, depending on the configuration either the
     *                field 'contact_email' from the realty record or the
     *                owner's email address, will be empty if no email address
     *                was found or if the realty object is not initialized
     */
    protected function getContactEmailFromRealtyObject()
    {
        if (!$this->realtyObject instanceof Objectimmo || $this->realtyObject->isDead()) {
            return '';
        }

        $emailAddress = $this->realtyObject->getProperty('contact_email');

        if ($this->mayUseOwnerData()) {
            try {
                $emailAddress = $this->realtyObject->getOwner()->getEmailAddress();
            } catch (\Tx_Oelib_Exception_NotFound $exception) {
            }
        }

        return $emailAddress;
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
     * Sets the contact email address of a realty object.
     *
     * @param string $address contact email address, must not be empty
     *
     * @return void
     */
    private function setContactEmailOfRealtyObject($address)
    {
        if (!$this->realtyObject instanceof Objectimmo) {
            return;
        }

        $this->realtyObject->setProperty('contact_email', $address);
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

