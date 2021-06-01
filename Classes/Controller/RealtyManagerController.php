<?php

namespace ChrisGruen\RealtyManager\Controller;

use ChrisGruen\RealtyManager\Domain\Repository\ObjectRepository;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class RealtyManagerController extends ActionController
{
    private $objectimmosRepository;
    
    /**
     * Inject the content repository
     *
     * @param ChrisGruen\RealtyManager\Domain\Repository\ObjectRepository $objectRepository
     */
    public function injectObjectRepository(ObjectImmosRepository $objectimmosRepository)
    {
        $this->objectimmosRepository = $objectimmosRepository;
    }
    
    public function listAction()
    {
        dump('end');
        exit();
        $objects = $this->objectimmosRepository->findAll();
        $this->view->assign('objects', $objects);
    } 
    
}
