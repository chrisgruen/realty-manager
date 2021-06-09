<?php
namespace ChrisGruen\RealtyManager\ViewHelpers;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class FeatureViewHelper
 */
class HouseTypeViewHelper extends AbstractViewHelper
{
    
    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        $this->registerArgument('houseuid', 'int', true);
    }
    
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
        ) {
            
            $house_uid = $arguments['houseuid'];
            
            //Apartment-Type;
            if ($house_uid > 0) {
                
                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_realtymanager_domain_model_house_types');
                
                $house_type = $queryBuilder
                    ->select('title')
                    ->from('tx_realtymanager_domain_model_house_types')
                    ->where($queryBuilder->expr()->eq('uid', $house_uid, \PDO::PARAM_INT))
                    ->execute()
                    ->fetchColumn(0);
                
                return $house_type;
            }
    }
}

