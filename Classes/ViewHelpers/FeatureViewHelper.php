<?php
namespace ChrisGruen\RealtyManager\ViewHelpers;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class FeatureViewHelper
 */
class FeatureViewHelper extends AbstractViewHelper
{
    /**
     * Array featurs available
     */
    protected static $booleanFeatureFields = [
        'balcony',
        'garden',
        'fitted_kitchen',
        'elevator',
        'barrier_free',
        'wheelchair_accessible',
        'suitable_for_the_elderly',
        'assisted_living',
    ];
    
    /**
     * Initialize arguments
     */
    public function initializeArguments()
    {
        $this->registerArgument('object', 'int', true);
    }
    
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
        ) {
            
            $uid_obj = $arguments['object'];
            $features = [];
            
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_realtymanager_domain_model_objectimmo');
                
            $object = $queryBuilder
                ->select('apartment_type', 'house_type')
                ->from('tx_realtymanager_domain_model_objectimmo')
                ->where($queryBuilder->expr()->eq('tx_realtymanager_domain_model_objectimmo.uid', $uid_obj, \PDO::PARAM_INT))
                ->execute()
                ->fetch();
         
                
            //Apartment-Type;
            if ($object['apartment_type'] > 0) {
                
                $apartment_type_id = $object['apartment_type'] ? $object['apartment_type'] : 0;
                
                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_realtymanager_domain_model_apartment_type');
                
                $apartment_type = $queryBuilder
                    ->select('title')
                    ->from('tx_realtymanager_domain_model_apartment_types')
                    ->where($queryBuilder->expr()->eq('uid', $apartment_type_id, \PDO::PARAM_INT))
                    ->execute()
                    ->fetchColumn(0);
                    
                $features[] = $apartment_type;
            }
            
            //House-Type;
            if ($object['house_type'] > 0) {
                
                $house_type_id = $object['house_type'] ? $object['house_type'] : 0;
                
                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_realtymanager_domain_model_house_types');
                
                $house_type = $queryBuilder
                    ->select('title')
                    ->from('tx_realtymanager_domain_model_house_types')
                    ->where($queryBuilder->expr()->eq('uid', $house_type_id, \PDO::PARAM_INT))
                    ->execute()
                    ->fetchColumn(0);
                
                $features[] = $house_type;
            }
            
            //Garage-Type;
            if ($object['garage_type'] > 0) {
                
                $garage_type_id = $object['garage_type'] ? $object['garage_type'] : 0;
                
                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_realtymanager_domain_model_house_types');
                
                $garage_type = $queryBuilder
                    ->select('title')
                    ->from('tx_realtymanager_domain_model_house_types')
                    ->where($queryBuilder->expr()->eq('uid', $garage_type_id, \PDO::PARAM_INT))
                    ->execute()
                    ->fetchColumn(0);
                
                $features[] = $garage_type;
            }
            
            foreach (static::$booleanFeatureFields as $key) {
                //echo $key."<br />";
            }

        return implode(', ', $features);
        //return implode('Hallo');
    }
}
