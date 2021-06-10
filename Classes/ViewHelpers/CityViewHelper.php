<?php
namespace ChrisGruen\RealtyManager\ViewHelpers;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class FeatureViewHelper
 */
class CityViewHelper extends AbstractViewHelper
{
    
    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        $this->registerArgument('cityuid', 'int', true);
    }
    
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
        ) {
            
            $city_uid = $arguments['cityuid'];
            
            if ($city_uid > 0) {
                
                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_realtymanager_domain_model_house_types');
                
                $city = $queryBuilder
                    ->select('title')
                    ->from('tx_realtymanager_domain_model_cities')
                    ->where($queryBuilder->expr()->eq('uid', $city_uid, \PDO::PARAM_INT))
                    ->execute()
                    ->fetchColumn(0);
                
                return $city;
            }
    }
}

