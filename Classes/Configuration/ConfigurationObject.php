<?php

namespace ChrisGruen\RealtyManager\Configuration;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConfigurationObject
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
    protected $paginateItemsPerPage = 6;

    /** @var boolean */
    protected $ajaxPaging = false;

    public function getPaginateItemsPerPage()
    {
        return (int)$this->paginateItemsPerPage;
    }

    public function getAjaxPaging()
    {
        return $this->ajaxPaging;
    }
}
