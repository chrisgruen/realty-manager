<?php
namespace ChrisGruen\RealtyManager\ViewHelpers;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class FeatureViewHelper
 */
class DistrictViewHelper extends AbstractViewHelper
{
    
    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        $this->registerArgument('districtuid', 'int', true);
    }
    
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
        ) {
            
            $district_uid = $arguments['districtuid'];
            
            if ($district_uid > 0) {
                
                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_realtymanager_domain_model_districts');
                
                $district = $queryBuilder
                    ->select('title')
                    ->from('tx_realtymanager_domain_model_districts')
                    ->where($queryBuilder->expr()->eq('uid', $district_uid, \PDO::PARAM_INT))
                    ->execute()
                    ->fetchColumn(0);
                
                return $district;
            }
    }
}


