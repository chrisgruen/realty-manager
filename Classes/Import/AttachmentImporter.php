<?php

namespace ChrisGruen\RealtyManager\Import;

use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use ChrisGruen\RealtyManager\Domain\Model\Objectimmo;
use ChrisGruen\RealtyManager\Domain\Repository\ObjectimmoRepository;
use ChrisGruen\RealtyManager\Configuration\ConfigurationImport;

class AttachmentImporter
{
    
    /**
     * @var int
     */
    private $uidObject = 0;
    
    /**
     * @var int
     */
    private $pidObject = 0;
    
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
        $this->settings = GeneralUtility::makeInstance(ConfigurationImport::class);
    }
    

    public function startTransaction()
    {
        $this->assertNoTransactionIsInProgress();
        $this->transactionIsInProgress = true;

        $object = $this->getUIdRecord();
        $this->uidObject = $object['uid'];
        $this->pidObject = $object['pid'];
    }
    
        
    /**
     *
     * @return uid object record
     */
    public function getUIdRecord()
    {
        $obj_number = $this->record['object_number'];
        $PidEmployer = $this->objectimmoRepository->getPidEmployer($this->ownerId);
        $object = $this->objectimmoRepository->getObject($obj_number, $PidEmployer);

        return $object;
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
    public function addAttachment($fileExtractionPath, $employer_folder, $title)
    {
        //$import_attachement = $fullPath;
        $import_attachement = $fileExtractionPath;
        $realty_store_folder = $this->settings->getResourceFolderExporter().'/'.$employer_folder;
                
        $resourceFactory = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\ResourceFactory::class);
        $storage = $resourceFactory->getDefaultStorage();       
        $pathExists = $storage->hasFolder($realty_store_folder);
        
        if($pathExists) {
            $file_name = basename($fileExtractionPath);
            $file = $storage->getFile($import_attachement);
            $folder = $storage->getFolder($realty_store_folder);
                   
            /* check if file exist */
            $base_path = realpath(__DIR__ . '/../../../../../');
            $file_exist = $base_path.'/fileadmin/'.$realty_store_folder.'/'.$file_name;
    
            if (file_exists($file_exist)) {
                unlink($file_exist);
                $copiedFile = $file->copyTo($folder);
            } else {
                $copiedFile = $file->copyTo($folder);
            }
            
            //get uid from table sys_file
            $file_path = $realty_store_folder.'/'.$file_name;
            $get_file_uid = $this->objectimmoRepository->getFileUid($file_path); 
            $set_file_object_relation = $this->objectimmoRepository->setFileObjectRelation($this->uidObject, $this->pidObject, $get_file_uid, $title);
            
            if($set_file_object_relation) {
                //echo "File relation created in table: sys_file_reference<br />";
            }

            return true;
        } else {
            return false;
        }
        
        $this->assertTransactionIsInProgress();
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
        $this->assertTransactionIsInProgress();
        $this->transactionIsInProgress = false;
    }

    
    /**
     * @return ResourceFactory
     */
    protected function getResourceFactory(): ResourceFactory
    {
        return GeneralUtility::makeInstance(ResourceFactory::class);
    }
}
