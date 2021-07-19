<?php

namespace ChrisGruen\RealtyManager\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ObjectimmoRepository extends Repository
{
    public function getAllObjects()
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_realtymanager_domain_model_objectimmo');
        $sql = "SELECT *, obj.uid as uid, obj.title as title FROM tx_realtymanager_domain_model_objectimmo obj
                LEFT JOIN pages p on obj.pid = p.uid
                WHERE obj.hidden = 0 AND obj.deleted = 0 AND p.hidden = 0 AND p.deleted = 0
                ORDER BY obj.uid DESC";
        
        $objects = $connection->executeQuery($sql)->fetchAll();
        return $objects;
    }
    
    public function getAllObjectsBySearch($form_data)
    {   
        $house_type = isset($form_data['house_type']) ? $form_data['house_type'] : 0;
        $apartment_type = isset($form_data['apartment_type']) ? $form_data['apartment_type'] : 0;
        $employer_page = isset($form_data['employer']) ? $form_data['employer'] : 0;
        $city = isset($form_data['city']) ? $form_data['city'] : 0;
        $district = isset($form_data['district']) ? $form_data['district'] : 0;
        $furnished = isset($form_data['furnished']) ? $form_data['furnished'] : 0;
        $fitted_kitchen = isset($form_data['fitted_kitchen']) ? $form_data['fitted_kitchen'] : 0;
        $barrier_free = isset($form_data['barrier_free']) ? $form_data['barrier_free'] : 0;
        $cleaning = isset($form_data['cleaning']) ? $form_data['cleaning'] : 0;
        $bicycleroom = isset($form_data['bicycleroom']) ? $form_data['bicycleroom'] : 0;
        $washingroom = isset($form_data['washingroom']) ? $form_data['washingroom'] : 0;
        $rent_from = isset($form_data['rent_from']) && is_numeric($form_data['rent_from']) ? $form_data['rent_from'] : 0;
        $rent_to = isset($form_data['rent_to']) && is_numeric($form_data['rent_to']) ? $form_data['rent_to'] : 99999;
        $living_area_from = isset($form_data['living_area_from']) && is_numeric($form_data['living_area_from']) ? $form_data['living_area_from'] : 0;
        $living_area_to = isset($form_data['living_area_to']) && is_numeric($form_data['living_area_to']) ? $form_data['living_area_to'] : 99999;
        
         
        $add_where = '';
        if($house_type > 0) {$add_where .= ' AND house_type = '.$house_type.'';}
        if($apartment_type > 0) {$add_where .= ' AND apartment_type = '.$apartment_type.'';}
        if($employer_page > 0) {$add_where .= ' AND obj.pid = '.$employer_page.'';}
        if ($city > 0) {$add_where .= ' AND city = '.$city.'';} 
        if ($district > 0) { $add_where .= ' AND district = '.$district.'';} 
        if ($furnished > 0) { $add_where .= ' AND furnished = 1';}
        if ($fitted_kitchen > 0) { $add_where .= ' AND fitted_kitchen = 1';}
        if ($barrier_free > 0) { $add_where .= ' AND barrier_free = 1';}
        if ($cleaning > 0) { $add_where .= ' AND cleaning = 1';}
        if ($bicycleroom > 0) { $add_where .= ' AND bicycleroom = 1';}
        if ($washingroom > 0) { $add_where .= ' AND washingroom = 1';}
        if ($rent_to < 99999) {
            $add_where .= ' AND rent_excluding_bills BETWEEN '.$rent_from.' AND '.$rent_to.'';
        } else {
            $add_where .= ' AND rent_excluding_bills > '.$rent_from.'';
        }
        if ($living_area_to < 99999) {
            $add_where .= ' AND living_area BETWEEN '.$living_area_from.' AND '.$living_area_to.'';
        } else {
            $add_where .= ' AND living_area > '.$living_area_from.'';
        }
                
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_chessmanager_domain_model_result');
        $sql = "SELECT *, obj.uid as uid, obj.title as title FROM tx_realtymanager_domain_model_objectimmo obj
                LEFT JOIN pages p on obj.pid = p.uid
                WHERE obj.hidden = 0 AND obj.deleted = 0 AND p.hidden = 0 AND p.deleted = 0
                $add_where
                ORDER BY obj.uid DESC";

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
                INNER JOIN sys_file_metadata fm on fm.file = f.uid
                WHERE uid_foreign = '".$uid."'
                AND f.extension <> 'pdf'
                ORDER BY sorting_foreign";
        
        $images = $connection->executeQuery($sql)->fetchAll();
        return $images;
    }

    /**
     * get HouseTypes
     * Data from Table "tx_realtymanager_domain_model_house_types"
     * @return array
     */
    public function getHouseTypes() {
        
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_realtymanager_domain_model_house_types');
        $sql = "SELECT uid, title from tx_realtymanager_domain_model_house_types order by uid";
        
        $housetypes = $connection->executeQuery($sql)->fetchAll();
        
        return $housetypes;
    }
    
    /**
     * get ApartmentTypes
     * Data from Table "tx_realtymanager_domain_model_apartment_types"
     * @return array
     */
    public function getApartmentTypes() {
        
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_realtymanager_domain_model_apartment_types');
        $sql = "SELECT uid, title from tx_realtymanager_domain_model_apartment_types order by uid";
        
        $apartmenttypes = $connection->executeQuery($sql)->fetchAll();
        
        return $apartmenttypes;
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
    
    public function checkOwnerAnid($employer_folder, $ownerId) {
        
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_realtymanager_domain_model_employer');
        $sql = "SELECT openimmo_anid from tx_realtymanager_domain_model_employer
                    WHERE import_folder = '".$employer_folder."'";
        
        $employer_anid_sql = $connection->executeQuery($sql)->fetch();
        $employer_anid = $employer_anid_sql['openimmo_anid'];
        
        if ($ownerId == $employer_anid) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * get  $pid_be_employer
     * Data from Table "tx_realtymanager_domain_model_employer"
     * @return int
     */
    public function getPidEmployer($ownerId) {
        
        $queryOwner = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_realtymanager_domain_model_employer');
        $pid_be_employer = $queryOwner
                            ->select('pid_be_user')
                            ->from('tx_realtymanager_domain_model_employer')
                            ->where($queryOwner->expr()->eq('openimmo_anid', $queryOwner->createNamedParameter($ownerId)))
                            ->execute()
                            ->fetchColumn(0);
                       
        
        return $pid_be_employer;
    }
    
    /**
     * get  $uid from object
     * Data from Table "tx_realtymanager_domain_model_objectimmo"
     * @return int
     */
    public function getObject($obj_number, $PidEmployer) {
        
        $queryUidObject = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_realtymanager_domain_model_objectimmo');
        $uidObject = $queryUidObject
                        ->select('uid', 'pid')
                        ->from('tx_realtymanager_domain_model_objectimmo')
                        ->where($queryUidObject->expr()->eq('object_number', $queryUidObject->createNamedParameter($obj_number)))
                        ->where($queryUidObject->expr()->eq('pid', $queryUidObject->createNamedParameter($PidEmployer)))
                        ->orderBy('uid', 'DESC')
                        ->execute()
                        ->fetch();
        
        return $uidObject;
    }
    
    /**
     * set a new Object
     * Data from Table "tx_realtymanager_domain_model_objectimmo"
     * @return void
     */
    public function setNewObject($ownerId, array $obj_insert) {
        
        $pid_be_employer = 0;
        $object_number = '';
        $object_number = $obj_insert['object_number'];

        $pid_be_employer = $this->getPidEmployer($ownerId);

        if ($pid_be_employer > 0) {
            
            $queryCheckEntry = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_realtymanager_domain_model_objectimmo');
            $checkEntry = $queryCheckEntry
                            ->select('object_number')
                            ->from('tx_realtymanager_domain_model_objectimmo')
                            ->where($queryCheckEntry->expr()->eq('object_number', $queryCheckEntry->createNamedParameter($object_number)))
                            ->execute()
                            ->fetchColumn(0);
            
            
            if ($checkEntry == Null) {
            
                $dataToInsert = $obj_insert;
                $dataToInsert['pid'] = $pid_be_employer;
                $dataToInsert['tstamp'] = $GLOBALS['SIM_EXEC_TIME'];
                $dataToInsert['crdate'] = $GLOBALS['SIM_EXEC_TIME'];
                
                $dataToInsert['house_type'] = $this->getRealitionUid('tx_realtymanager_domain_model_house_types', $obj_insert['house_type']);
                $dataToInsert['apartment_type'] = $this->getRealitionUid('tx_realtymanager_domain_model_apartment_types', $obj_insert['apartment_type']);
                $dataToInsert['city'] = $this->getRealitionUid('tx_realtymanager_domain_model_cities', $obj_insert['city']);
                $dataToInsert['district'] = $this->getRealitionUid('tx_realtymanager_domain_model_districts', $obj_insert['district']);
                $dataToInsert['pets'] = $this->getRealitionUid('tx_realtymanager_domain_model_pets', $obj_insert['pets']);
                $dataToInsert['garage_type'] = $this->getRealitionUid('tx_realtymanager_domain_model_car_places', $obj_insert['garage_type']);
                
                
                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_realtymanager_domain_model_objectimmo');
                $affectedRows = $queryBuilder
                                ->insert('tx_realtymanager_domain_model_objectimmo')
                                ->values($dataToInsert)
                                ->execute();
                
                return true; 
            }
            return false; 
        } else {
            return false;
        }
    }

    /**
     * delete Object
     * Data from Table "tx_realtymanager_domain_model_objectimmo"
     * @return void
     */
    public function delObject($ownerId, $obj_number) {

        $pid_be_employer = 0;
        $object_number = '';
        $pid_be_employer = $this->getPidEmployer($ownerId);

        if($obj_number != '') {
            $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_realtymanager_domain_model_objectimmo');
            $sql_del_obj = "DELETE FROM tx_realtymanager_domain_model_objectimmo 
                            WHERE object_number = '".$obj_number."'
                            AND pid = '".$pid_be_employer."' ";

            $del_obj = $connection->executeQuery($sql_del_obj);
            return true;
        }
        return false;
    }

    /**
     * delete all Objects from Employer
     * Data from Table "tx_realtymanager_domain_model_objectimmo"
     * @return void
     */
    public function delAllObjects($employer_pid) {

        if($employer_pid > 0) {
            $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_realtymanager_domain_model_objectimmo');
            $sql_all_objects = "DELETE FROM tx_realtymanager_domain_model_objectimmo 
                                WHERE pid = '".$employer_pid."' ";

            $del_all_objects = $connection->executeQuery($sql_all_objects);
            return true;
        }
    }
    
    /**
     * set a get realition uid of table
     * Data from Table $table
     *
     */
    public function getRealitionUid($table, $field_val) {

        $queryGetUId = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $uid = $queryGetUId->select('uid')
                            ->from($table)
                            ->where($queryGetUId->expr()->eq('title', $queryGetUId->createNamedParameter($field_val)))
                            ->execute()
                            ->fetchColumn(0);
        
        return $uid;
    }
    
    /**
     * get sys_file.uid
     *
     */
    public function getFileUid($path) {
        
        $queryGetUId = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file');
        $uid = $queryGetUId->select('uid')
                            ->from('sys_file')
                            ->where($queryGetUId->expr()->eq('identifier', $queryGetUId->createNamedParameter($path)))
                            ->execute()
                            ->fetchColumn(0);
        
        return $uid;
    }
    
    /**
     * set FileObjectRelation
     *
     */
    public function setFileObjectRelation($uidObject, $pidObject, $uidFile, $title) {
        
        /* check file-relation */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_file_reference');
        $sql = "SELECT uid from sys_file_reference 
                WHERE pid = '".$pidObject."'  
                AND uid_local  = '".$uidFile."'
                AND uid_foreign  = '".$uidObject."' ";
        
        $sql_entry = $connection->executeQuery($sql)->fetch();
        
        if ($sql_entry == Null) {

            $dataToInsert['tstamp'] = $GLOBALS['SIM_EXEC_TIME'];
            $dataToInsert['crdate'] = $GLOBALS['SIM_EXEC_TIME'];
            $dataToInsert['pid'] = $pidObject;
            $dataToInsert['uid_local'] = $uidFile;
            $dataToInsert['uid_foreign'] = $uidObject;
            $dataToInsert['tablenames'] = 'tx_realtymanager_domain_model_objectimmo';
            $dataToInsert['fieldname'] = 'attachments';
            $dataToInsert['table_local'] = 'sys_file';

            $queryBuilder1 = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file_reference');
            $insertFileReferenz = $queryBuilder1
                                ->insert('sys_file_reference')
                                ->values($dataToInsert)
                                ->execute();

            $queryBuilder2 = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file_metadata');
            $updateFileMeta = $queryBuilder2
                                ->update('sys_file_metadata')
                                ->where(
                                    $queryBuilder2->expr()->eq('file', $uidFile)
                                )
                                ->set('title', $title)
                                ->set('description', $title)
                                ->set('alternative', $title)
                                ->execute();

            return true;
        }
        return false;
    }

    /**
     * clear sys_file, sys_file_metadata
     *
     */
    public function clearSysFiles($phrase) {

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_file');
        $sql = "SELECT uid from sys_file 
                WHERE identifier  LIKE '%".$phrase."%'";

        $del_files = $connection->executeQuery($sql)->fetchAll();

        $del_file_uids = [];
        foreach ($del_files as $key => $del_file) {
            $file_uid = $del_file['uid'];
            $sql_del1 = "DELETE FROM sys_file_metadata WHERE file = '".$file_uid."' ";
            $del_files1 = $connection->executeQuery($sql_del1);
            $sql_del2 = "DELETE FROM sys_file WHERE uid = '".$file_uid."' ";
            $del_files2 = $connection->executeQuery($sql_del2);
        }
        return;
    }

    /**
     * get PriceRange
     * @return array
     */
    public function getPriceRange() {        
        $prices = [];
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
