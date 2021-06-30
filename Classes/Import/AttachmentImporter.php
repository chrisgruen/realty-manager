<?php

namespace ChrisGruen\RealtyManager\Import;

use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use ChrisGruen\RealtyManager\Domain\Model\Objectimmo;
use ChrisGruen\RealtyManager\Domain\Repository\ObjectimmoRepository;

class AttachmentImporter
{
    
    /**
     * @var string
     */
    private $ownerId = '';
    
    /**
     * @var string[]
     */
    private $record = [];

    /**
     * @var bool
     */
    private $transactionIsInProgress = false;

    /**
     * sub keys: fullPath and title
     *
     * @var string[][]
     */
    private $attachmentsToBeAdded = [];

    /**
     * For better performance, the keys are the UIDs, and the values are "true"
     *
     * @var bool[]
     */
    private $uidsOfFilesToRemove = [];
    
    private $objectimmoRepository;


    public function __construct($ownerId, $record, ObjectimmoRepository $objectimmoRepository)
    {
        $this->ownerId = $ownerId;
        $this->record = $record;
        $this->objectimmoRepository = $objectimmoRepository;
    }
    

    public function startTransaction()
    {
        $this->assertNoTransactionIsInProgress();
        $this->transactionIsInProgress = true;

        $uidObject = $this->getUIdRecord();
        echo $uidObject . " :: ";
        //$this->clearAttachments($uidObject);
        //$this->extractAttachmentUid($uidObject);
    }
    
        
    /**
     *
     * @return uid object record
     */
    public function getUIdRecord()
    {
        $obj_number = $this->record['object_number'];
        $PidEmployer = $this->objectimmoRepository->getPidEmployer($this->ownerId);
        $uidObject = $this->objectimmoRepository->getUidObject($obj_number, $PidEmployer);
        
        return $uidObject;
    }
    
    /**
     *
     * @param string $fullPath
     * @param string $title
     *
     * @return void
     *
     * @throws \BadMethodCallException
     */
    public function addAttachment($fileExtractionPath, $title)
    {
        //$import_attachement = $fullPath;
        $import_attachement = $fileExtractionPath;
        $realty_store_folder = 'user_upload';
        
        $resourceFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\ResourceFactory::class);
        $storage = $resourceFactory->getDefaultStorage();
        $file = $storage->getFile($import_attachement);
        $folder = $storage->getFolder($realty_store_folder);
        
        /* check if file exist */
        $base_path = $_SERVER['DOCUMENT_ROOT'];
        $file_exist = $base_path.'/fileadmin/'.$realty_store_folder.'/'.basename($fileExtractionPath);

        if (file_exists($file_exist)) {
            echo $import_attachement ." -> File already copied <br />";
        } else {
            $copiedFile = $file->copyTo($folder);
            echo $import_attachement . " :: " .$title. " -> Files copied <br />";
        }

        //$this->assertTransactionIsInProgress();
        
        //$this->attachmentsToBeAdded[] = ['fullPath' => $fullPath, 'title' => $title];
    }

    /**
     * @return void
     *
     * @throws \BadMethodCallException
     */
    private function assertNoTransactionIsInProgress()
    {
        if ($this->transactionIsInProgress) {
            throw new \BadMethodCallException(
                'This method cannot be called while a transaction is in progress.',
                1550840989
            );
        }
    }


    /**
     * @return void
     *
     * @throws \BadMethodCallException
     */
    private function assertTransactionIsInProgress()
    {
        if (!$this->transactionIsInProgress) {
            throw new \BadMethodCallException(
                'This method cannot be called without a transaction. Please call startTransaction first.',
                1550840936
            );
        }
    }

    /**
     * @return void
     *
     * @throws \BadMethodCallException
     */
    public function finishTransaction()
    {
        echo "finish Transaction";
        exit();
        
        $this->assertTransactionIsInProgress();

        $this->processAddedAttachments();
        $this->processRemovedAttachments();
        $this->realtyObject->writeToDatabase();

        $this->transactionIsInProgress = false;
    }

    /**
     * @return void
     */
    private function processAddedAttachments()
    {
        foreach ($this->attachmentsToBeAdded as $attachment) {
            $fullPath = $attachment['fullPath'];
            $baseName = \basename($fullPath);
            $existingAttachment = $this->realtyObject->getAttachmentByBaseName($baseName);
            if ($existingAttachment !== null) {
                $fileUid = $existingAttachment->getOriginalFile()->getUid();
                unset($this->uidsOfFilesToRemove[$fileUid]);
            } else {
                if (!\file_exists($fullPath)) {
                    continue;
                }
                $title = $attachment['title'];
                $this->realtyObject->addAndSaveAttachment($fullPath, $title);
            }
        }
    }

    /**
     * @return void
     */
    private function processRemovedAttachments()
    {
        foreach ($this->uidsOfFilesToRemove as $uid => $_) {
            $this->realtyObject->removeAttachmentByFileUid($uid);
        }
    }
    
    
    /**
     * clear attachements
     */
    public function clearAttachments($uidObject)
    {
        if ($uidObject < 1) {
            return [];
        }
        
        $find_attachements = $this->getFileRepository()->findByRelation('tx_realtymanager_domain_model_objectimmo', 'attachments', $uidObject);
    }
    
    /**
     * @return FileReference[]
     */
    public function getAttachments($uidObject)
    {
        if ($uidObject < 1) {
            return [];
        }
        
        return $this->getFileRepository()->findByRelation('tx_realtymanager_domain_model_objectimmo', 'attachments', $uidObject);
    }
    
    /**
     * @return ResourceFactory
     */
    protected function getResourceFactory(): ResourceFactory
    {
        return GeneralUtility::makeInstance(ResourceFactory::class);
    }
}
