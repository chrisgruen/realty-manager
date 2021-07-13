<?php

namespace ChrisGruen\RealtyManager\Controller;

use ChrisGruen\RealtyManager\Domain\Repository\ObjectimmoRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Page\AssetCollector;
use ChrisGruen\RealtyManager\Configuration\ConfigurationObject;

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
    public function startformAction()
    {
        /* get opt-select housetypes */
        $dataHouseTypes = $this->objectimmoRepository->getHouseTypes();
        $housetypes = $this->selectHousetypes($dataHouseTypes);

        /* get opt-select pricerange */
        $dataPrices = $this->objectimmoRepository->getPriceRange();
        $rentprices = $this->selectPrices($dataPrices);
        $price_key_last = array_key_last($rentprices);
        $price_last = $rentprices[$price_key_last];
        $price_last = $price_last->key;

        /* get opt-select arearange */
        $dataAreas = $this->objectimmoRepository->getAreaRange();
        $areas = $this->selectAreas($dataAreas);
        $area_key_last = array_key_last($areas);
        $area_last = $areas[$area_key_last];
        $area_last = $area_last->key;

        $this->view->assign('housetypes', $housetypes);
        $this->view->assign('rentprices', $rentprices);
        $this->view->assign('price_last', $price_last);
        $this->view->assign('areas', $areas);
        $this->view->assign('area_last', $area_last);
    }

    /**
     *
     * return view for formAction (show Searchform)
     */
    public function formAction()
    {  
        /* get opt-select housetypes */
        $dataHouseTypes = $this->objectimmoRepository->getHouseTypes();
        $housetypes = $this->selectHousetypes($dataHouseTypes);
        
        /* get opt-select apartmenttypes */
        $dataApartmentTypes = $this->objectimmoRepository->getApartmentTypes();
        $apartmenttypes = $this->selectApartmenttypes($dataApartmentTypes);
        
        /* get opt-select employers */
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
        $price_key_last = array_key_last($rentprices);
        $price_last = $rentprices[$price_key_last];
        $price_last = $price_last->key;

        /* get opt-select arearange */
        $dataAreas = $this->objectimmoRepository->getAreaRange();
        $areas = $this->selectAreas($dataAreas);
        $area_key_last = array_key_last($areas);
        $area_last = $areas[$area_key_last];
        $area_last = $area_last->key;

        $this->view->assign('housetypes', $housetypes);
        $this->view->assign('apartmenttypes', $apartmenttypes);
        $this->view->assign('employers', $employers);
        $this->view->assign('cities', $cities);
        $this->view->assign('districts', $districts);
        $this->view->assign('rentprices', $rentprices);
        $this->view->assign('areas', $areas);
        $this->view->assign('price_last', $price_last);    
        $this->view->assign('area_last', $area_last); 
    } 
 
    /**
     *
     * return view for listAction (Random List)
     */ 
    public function listAction()
    {
        $form_data = $this->request->getArguments();
              
        $settings = GeneralUtility::makeInstance(ConfigurationObject::class);
        $itemsPerPage = (int)$settings->getPaginateItemsPerPage();

        if($form_data) {
            $objects = $this->objectimmoRepository->getAllObjectsBySearch($form_data);
        } else {
            $objects = $this->objectimmoRepository->getAllObjects();
        }
        
        $count_objects = count($objects);

        $this->view->assign('objects', $objects);
        $this->view->assign('count_objects', $count_objects);
        $this->view->assign('itemsPerPage', $itemsPerPage);
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

        $this->forward('list', 'RealtyManager', $this->request->getControllerExtensionName(), $form_data);
        //$this->view->assign('objects', $objects);
    }
    
    /**
     *
     * return view (ajax) for searchAction
     */
    public function ajaxsearchAction()
    {
        $form_data = $this->request->getArguments();
        $count_objects = 0;
        
        if($form_data) {
            $objects = $this->objectimmoRepository->getAllObjectsBySearch($form_data);
            $count_objects = count($objects);
        }

        $settings = GeneralUtility::makeInstance(ConfigurationObject::class);
        $ajaxPaging = (int)$settings->getAjaxPaging();
        $itemsPerPage = (int)$settings->getPaginateItemsPerPage();

        $this->view->setTemplatePathAndFilename('typo3conf/ext/' .$this->request->getControllerExtensionKey() .'/Resources/Private/Templates/Ajaxsearch.html');

        $this->view->assign('objects', $objects);
        $this->view->assign('count_objects', $count_objects);
        $this->view->assign('ajaxPaging', $ajaxPaging);
        $this->view->assign('itemsPerPage', $itemsPerPage);
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
        
        $this->view->setTemplatePathAndFilename('typo3conf/ext/' .$this->request->getControllerExtensionKey() .'/Resources/Private/Templates/Ajaxselectdistrict.html');
        $ajaxcontent = $this->view->assign('districts', $districts);
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
     * prepare housetypes for select box
     *
     * @return array
     */
    public function selectHousetypes($dataHouseTypes) {
        $housetypes = [];
        $housetype = new \stdClass();
        $housetype->key = 0;
        $housetype->value = "Alle";
        $housetypes[] = $housetype;
        foreach($dataHouseTypes as $dataHouseType) {
            $housetype = new \stdClass();
            $housetype->key = $dataHouseType['uid'];
            $housetype->value = $dataHouseType['title'];
            $housetypes[] = $housetype;
        }
        return $housetypes;
    }
    
    /**
     * prepare housetypes for select box
     *
     * @return array
     */
    public function selectApartmenttypes($dataApartmentTypes) {
        $apartmenttypes = [];
        $apartmenttype = new \stdClass();
        $apartmenttype->key = 0;
        $apartmenttype->value = "Alle";
        $apartmenttypes[] = $apartmenttype;
        foreach($dataApartmentTypes as $dataApartmentType) {
            $apartmenttype = new \stdClass();
            $apartmenttype->key = $dataApartmentType['uid'];
            $apartmenttype->value = $dataApartmentType['title'];
            $apartmenttypes[] = $apartmenttype;
        }
        
        return $apartmenttypes;
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
}
