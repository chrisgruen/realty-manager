<?php

namespace ChrisGruen\RealtyManager\Controller;

use ChrisGruen\RealtyManager\Domain\Repository\ObjectimmosRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class RealtyManagerController extends ActionController
{
    private $objectimmosRepository;
    
    /**
     * Inject the content repository
     *
     * @param ChrisGruen\RealtyManager\Domain\Repository\ObjectimmosRepository $objectRepository
     */
    public function injectObjectRepository(ObjectimmosRepository $objectimmosRepository)
    {
        $this->objectimmosRepository = $objectimmosRepository;
    }
    
    public function listAction()
    {
        $objects = $this->objectimmosRepository->findAll();
        $this->view->assign('objects', $objects);
    } 
    
}
