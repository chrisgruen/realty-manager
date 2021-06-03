<?php

namespace ChrisGruen\RealtyManager\Controller;

use ChrisGruen\RealtyManager\Domain\Repository\ObjectimmoRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

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
    
    public function formAction()
    {
        /* get opt-select cities */
        $dataCities = $this->objectimmoRepository->getCities();
        $cities = $this->selectCities($dataCities);
        
        /* get opt-select districts */
        $dataDistricts = $this->objectimmoRepository->getDistricts();
        $districts = $this->selectDistricts($dataDistricts);
        
        /* get opt-select pricerange */
        $dataPrices = $this->objectimmoRepository->getPriceRange();
        $rentprices = $this->selectPrices($dataPrices);
        
        /* get opt-select arearange */
        $dataAreas = $this->objectimmoRepository->getAreaRange();
        $areas = $this->selectAreas($dataAreas);
        
        $this->view->assign('cities', $cities);
        $this->view->assign('districts', $districts);
        $this->view->assign('rentprices', $rentprices);
        $this->view->assign('areas', $areas);
    } 
    
    public function listAction()
    {
        $objects = $this->objectimmoRepository->findAll();
        $this->view->assign('objects', $objects);
    } 
    
    /**
     * prepare cities for select box
     *
     * @return array
     */
    public function selectCities($dataCities) {
        $cities = [];
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
