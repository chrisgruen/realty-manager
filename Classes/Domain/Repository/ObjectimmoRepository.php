<?php

namespace ChrisGruen\RealtyManager\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ObjectimmoRepository extends Repository
{
    /**
     * get Cities
     * Data from Table "tx_realtymanager_domain_model_cities"
     * @return array
     */
    public function getCities() {
        
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_realtymanager_domain_model_objectimmo');
        $sql = "SELECT uid, title from tx_realtymanager_domain_model_cities order by uid";
        
        $cities = $connection->executeQuery($sql)->fetchAll();
        return $cities;
    }
    
    /**
     * get Districts
     * Data from Table "tx_realtymanager_domain_model_districts"
     * @return array
     */
    public function getDistricts($city_id) {
        
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_realtymanager_domain_model_objectimmo');
        $sql = "SELECT uid, title from tx_realtymanager_domain_model_districts WHERE city = '".$city_id."' order by title";
        
        $districts = $connection->executeQuery($sql)->fetchAll();
        return $districts;
    }
      
    
    /**
     * get PriceRange
     * @return array
     */
    public function getPriceRange() {        
        $prices = [];
        $prices[0] = '0 €';
        $prices[25] = '25 €';
        $prices[50] = '50 €';
        $prices[75] = '75 €';
        $prices[100] = '100 €';
        $prices[125] = '125 €';
        $prices[150] = '150 €';
        $prices[175] = '175 €';
        $prices[200] = '200 €';
        $prices[225] = '225 €';
        $prices[250] = '250 €';
        $prices[275] = '275 €';
        $prices[300] = '300 €';
        $prices[350] = '350 €';
        $prices[400] = '400 €';
        $prices[450] = '450 €';
        $prices[500] = '500 €';
        $prices[550] = '550 €';
        $prices[600] = '600 €';
        $prices[650] = '650 €';
        $prices[700] = '700 €';
        $prices[750] = '750 €';
        $prices[800] = '800 €';
        $prices[850] = '850 €';
        $prices[900] = '900 €';
        $prices[950] = '950 €';
        $prices[1000] = '1000 €';
        $prices[1100] = '1100 €';
        $prices[1200] = '1200 €';
        return $prices;
    }
    
    /**
     * get AreaRange
     * @return array
     */
    public function getAreaRange() {        
        $areas = [];
        $areas[10] = '10 m²';
        $areas[20] = '20 m²';
        $areas[30] = '30 m²';
        $areas[40] = '40 m²';
        $areas[50] = '50 m²';
        $areas[60] = '60 m²';
        $areas[70] = '70 m²';
        $areas[80] = '80 m²';
        $areas[90] = '90 m²';
        $areas[100] = '100 m²';
        $areas[110] = '110 m²';
        $areas[120] = '120 m²';
        $areas[130] = '130 m²';
        return $areas;
    }
}
