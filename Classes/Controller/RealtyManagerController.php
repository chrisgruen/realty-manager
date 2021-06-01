<?php

namespace ChrisGruen\RealtyManager\Controller;

use ChrisGruen\RealtyManager\Domain\Repository\ObjectRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class RealtyManagerController extends ActionController
{
    private $objectRepository;
    
    /**
     * Inject the content repository
     *
     * @param ChrisGruen\RealtyManager\Domain\Repository\ObjectRepository $objectRepository
     */
    public function injectObjectRepository(ObjectRepository $objectRepository)
    {
        $this->objectRepository = $objectRepository;
    }
    
    public function listAction()
    {
        $objects = $this->productRepository->findAll();
        $this->view->assign('objects', $objects);
    } 
    
}
