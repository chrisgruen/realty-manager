<?php
namespace ChrisGruen\RealtyManager\ViewHelpers;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class FeatureViewHelper
 */
class ApartmentTypeViewHelper extends AbstractViewHelper
{
    
    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        $this->registerArgument('apartmentuid', 'int', true);
    }
    
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
        ) {
            
            $appartment_uid = $arguments['apartmentuid'];
                       
            if ($appartment_uid > 0) {
                      
                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_realtymanager_domain_model_apartment_type');
                
                $apartment_type = $queryBuilder
                    ->select('title')
                    ->from('tx_realtymanager_domain_model_apartment_types')
                    ->where($queryBuilder->expr()->eq('uid', $appartment_uid, \PDO::PARAM_INT))
                    ->execute()
                    ->fetchColumn(0);
                
                return $apartment_type;
            }            
    }
}
