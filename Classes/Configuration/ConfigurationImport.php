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
                $configuration = $extensionConfiguration->get('realty');
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
    
    
    public function getStorageUidImporter(): int
    {
        return (int)$this->storageUidImporter;
    }
    
    public function getResourceFolderImporter(): string
    {
        return $this->resourceFolderImporter;
    }

    public function getResourceFolderExporter(): string
    {
        return $this->resourceFolderExporter ;
    }
}