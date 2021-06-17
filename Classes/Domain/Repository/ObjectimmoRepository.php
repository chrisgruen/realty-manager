<?php

namespace ChrisGruen\RealtyManager\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ObjectimmoRepository extends Repository
{
    
    public function getAllObjects()
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_chessmanager_domain_model_result');
        $sql = "SELECT * from tx_realtymanager_domain_model_objectimmo
                WHERE hidden = 0 AND deleted = 0
                ORDER BY uid DESC";
        
        $objects = $connection->executeQuery($sql)->fetchAll();
        return $objects;
    }
    
    public function getAllObjectsBySearch($form_data)
    {        
        $employer_page = isset($form_data['employer']) ? $form_data['employer'] : 0;
        $city = isset($form_data['city']) ? $form_data['city'] : 0;
        $district = isset($form_data['district']) ? $form_data['district'] : 0;
        $furnished = isset($form_data['furnished']) ? $form_data['furnished'] : 0;
        $fitted_kitchen = isset($form_data['fitted_kitchen']) ? $form_data['fitted_kitchen'] : 0;
        $barrier_free = isset($form_data['barrier_free']) ? $form_data['barrier_free'] : 0;
        $cleaning = isset($form_data['cleaning']) ? $form_data['cleaning'] : 0;
        $bicycleroom = isset($form_data['bicycleroom']) ? $form_data['bicycleroom'] : 0;
        $washingroom = isset($form_data['washingroom']) ? $form_data['washingroom'] : 0;
        
        
        $add_where = '';
        if($employer_page > 0) {$add_where .= ' AND pid = '.$employer_page.'';}
        if ($city > 0) {$add_where .= ' AND city = '.$city.'';} 
        if ($district > 0) { $add_where .= ' AND district = '.$district.'';} 
        if ($furnished > 0) { $add_where .= ' AND furnished = 1';}
        if ($fitted_kitchen > 0) { $add_where .= ' AND fitted_kitchen = 1';}
        if ($barrier_free > 0) { $add_where .= ' AND barrier_free = 1';}
        if ($cleaning > 0) { $add_where .= ' AND cleaning = 1';}
        if ($bicycleroom > 0) { $add_where .= ' AND bicycleroom = 1';}
        if ($washingroom > 0) { $add_where .= ' AND washingroom = 1';}
        
        
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_chessmanager_domain_model_result');
        $sql = "SELECT * from tx_realtymanager_domain_model_objectimmo
                WHERE hidden = 0 AND deleted = 0
                $add_where
                ORDER BY uid DESC";

        $objects = $connection->executeQuery($sql)->fetchAll();
        
        return $objects;
    }
    
    public function getEmployer($pid)
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_realtymanager_domain_model_employer');
        $sql = "SELECT * from tx_realtymanager_domain_model_employer
                WHERE pid_be_user = '".$pid."'";
        
        $employer = $connection->executeQuery($sql)->fetch();
        return $employer;
    }
    
    public function getImages($uid)
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_file_reference');
        $sql = "SELECT * from sys_file_reference fs
                INNER JOIN sys_file f on fs.uid_local = f.uid
                WHERE uid_foreign = '".$uid."'
                ORDER BY sorting_foreign";
        
        $images = $connection->executeQuery($sql)->fetchAll();
        return $images;
    }
    
    /**
     * get Employers
     * Data from Table "tx_realtymanager_domain_model_employer"
     * @return array
     */
    public function getEmployers() {
        
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_realtymanager_domain_model_employer');
        $sql = "SELECT uid, pid_be_user, company from tx_realtymanager_domain_model_employer order by uid";
        
        $employers = $connection->executeQuery($sql)->fetchAll();
        
        return $employers;
    }
    
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
        $prices['egal'] = 'egal';
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
        $areas['egal'] = 'egal';
        return $areas;
    }
}
