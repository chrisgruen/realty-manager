<?php

namespace ChrisGruen\RealtyManager\Configuration;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConfigurationImport
{
    
    /**
     * Fill the properties properly
     *
     * @param array $configuration em configuration
     */
    public function __construct(array $configuration = [])
    {
        if (empty($configuration)) {
            try {
                $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
                $configuration = $extensionConfiguration->get('realty_manager');
            } catch (\Exception $exception) {
                // do nothing
            }
        }
        
        foreach ($configuration as $key => $value) {
            if (property_exists(__CLASS__, $key)) {
                $this->$key = $value;
            }
        }
    }
    
    /** @var int */
    protected $storageUidImporter = 1;
    
    /** @var string */
    protected $resourceFolderImporter = '/import';

    /** @var string */
    protected $resourceFolderExporter = '/realty';

    /** @var int */
    protected $logEmailSendContacts = 0;

    /** @var int */
    protected $logEmailSendTechnEmail = 1;
    
    /** @var string */
    protected $fromEmail = 'cg@romonta-schach.de';
    
    /** @var string */
    protected $toTestEmail = 'cg@lubey.de'; // if empty takes the "techn_email" from XML-Import-File
    
    
    public function getStorageUidImporter()
    {
        return $this->storageUidImporter;
    }
    
    public function getResourceFolderImporter()
    {
        return $this->resourceFolderImporter;
    }

    public function getResourceFolderExporter()
    {
        return $this->resourceFolderExporter;
    }

    public function getLogEmailSendContacts()
    {
        return $this->logEmailSendContacts;
    }

    public function getLogEmailSendTechnEmail()
    {
        return $this->logEmailSendTechnEmail;
    }
    
    public function getFromEmail()
    {
        return $this->fromEmail;
    }
    
    public function getToTestEmaill()
    {
        return $this->toTestEmail;
    }
}