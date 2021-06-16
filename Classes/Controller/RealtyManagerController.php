<?php

namespace ChrisGruen\RealtyManager\Controller;

use ChrisGruen\RealtyManager\Domain\Repository\ObjectimmoRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Page\AssetCollector;

class RealtyManagerController extends ActionController
{
    private $objectimmoRepository;
    
    /**
     * Inject the objectimmo repository
     *
     * @param ChrisGruen\RealtyManager\Domain\Repository\ObjectimmoRepository $objectimmoRepository
     */
    public function injectObjectimmoRepository(ObjectimmoRepository $objectimmoRepository)
    {
        $this->objectimmoRepository = $objectimmoRepository;
    }
    
    /**
     *
     * return view for formAction (show Searchform)
     */
    public function formAction()
    {    
        /* get opt-select cities */
        $dataEmployers = $this->objectimmoRepository->getEmployers();
        $employers = $this->selectEmployers($dataEmployers);
        
        /* get opt-select cities */
        $dataCities = $this->objectimmoRepository->getCities();
        $cities = $this->selectCities($dataCities);
        
        /* get opt-select districts */
        $dataDistricts = $this->objectimmoRepository->getDistricts(1);
        $districts = $this->selectDistricts($dataDistricts);
        
        /* get opt-select pricerange */
        $dataPrices = $this->objectimmoRepository->getPriceRange();
        $rentprices = $this->selectPrices($dataPrices);
        
        /* get opt-select arearange */
        $dataAreas = $this->objectimmoRepository->getAreaRange();
        $areas = $this->selectAreas($dataAreas);

        $this->view->assign('employers', $employers);
        $this->view->assign('cities', $cities);
        $this->view->assign('districts', $districts);
        $this->view->assign('rentprices', $rentprices);
        $this->view->assign('areas', $areas);
                
    } 
 
    /**
     *
     * return view for listAction (Random List)
     */ 
    public function listAction()
    {
        //$objects = $this->objectimmoRepository->findAll()
        $objects = $this->objectimmoRepository->getAllObjects();
        $this->view->assign('objects', $objects);
    } 
    
    /**
     *
     * return view for searchAction
     */ 
    public function searchAction()
    {
        $form_data = $this->request->getArguments();

        if($form_data) {
            $objects = $this->objectimmoRepository->getAllObjectsBySearch($form_data);
        }
               
        $this->view->assign('objects', $objects);
    }
    
    /**
     *
     * return view for deailAction
     */   
    public function detailAction(\ChrisGruen\RealtyManager\Domain\Model\Objectimmo $objUid)
    {        
        $uid = $objUid->getUid();
        $pid = $objUid->getPid();
        $object =  $this->objectimmoRepository->findByUid($uid);
        $images = $this->objectimmoRepository->getImages($uid);
        $employer = $this->objectimmoRepository->getEmployer($pid);
        
        $this->view->assign('object', $object);
        $this->view->assign('images', $images);
        $this->view->assign('employer', $employer);
    }
    
    /**
     * prepare employers for select box
     *
     * @return array
     */
    public function selectEmployers($dataEmployers) {
        $employers = [];
        $employer = new \stdClass();
        $employer->key = 0;
        $employer->value = "Alle";
        $employers[] = $employer;
        foreach($dataEmployers as $dataEmployer) {
            $employer = new \stdClass();
            $employer->key = $dataEmployer['pid_be_user'];
            $employer->value = $dataEmployer['company'];
            $employers[] = $employer;
        }
        return $employers;
    }
    
    
    /**
     * prepare cities for select box
     *
     * @return array
     */
    public function selectCities($dataCities) {
        $cities = [];
        $city = new \stdClass();
        $city->key = 0;
        $city->value = "Alle";
        $cities[] = $city;
        foreach($dataCities as $dataCity) {  
            $city = new \stdClass();
            $city->key = $dataCity['uid'];
            $city->value = $dataCity['title'];
            $cities[] = $city;
        }
        return $cities;
    }   
    
    /**
     * prepare districts for select box
     *
     * @return array
     */
    public function selectDistricts($dataDistricts) {
        $districts = [];
        $district = new \stdClass();
        $district->key = 0;
        $district->value = "Alle";
        $districts[] = $district;
        foreach($dataDistricts as $dataDistrict) {
            $district = new \stdClass();           
            $district->key = $dataDistrict['uid'];
            $district->value = $dataDistrict['title'];
            $districts[] = $district;
        }
        return $districts;
    }
    
    /**
     * prepare prices for select box
     *
     * @return array
     */
    public function selectPrices($dataPrices) {
        $prices = [];
        foreach($dataPrices as $key => $dataPrice) {
            $price = new \stdClass();
            $price->key = $key;
            $price->value = $dataPrice;
            $prices[] = $price;
        }
        return $prices;
    }
    
    /**
     * prepare prices for select box
     *
     * @return array
     */
    public function selectAreas($dataAreas) {
        $areas = [];
        foreach($dataAreas as $key => $dataArea) {
            $area = new \stdClass();
            $area->key = $key;
            $area->value = $dataArea;
            $areas[] = $area;
        }
        return $areas;
    }
    
    /**
     * action ajaxCall District options
     * 
     */
    public function ajaxselectdistrictAction()
    {
        $cityId = isset($_GET['cityId']) ? $_GET['cityId'] : 0;
        $dataDistricts = $this->objectimmoRepository->getDistricts($cityId);
        $districts = $this->selectDistricts($dataDistricts);
        $ajaxcontent = $this->view->assign('districts', $districts);
    }
}
