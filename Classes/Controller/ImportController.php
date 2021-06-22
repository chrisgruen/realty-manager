<?php

namespace ChrisGruen\RealtyManager\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
//use TYPO3\CMS\Backend\View\BackendTemplateView;
//use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
//use ChrisGruen\RealtyManager\Import\OpenImmoImport;

use TYPO3\CMS\Backend\Clipboard\Clipboard;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\Components\Menu\Menu;
use TYPO3\CMS\Backend\Utility\BackendUtility as BackendUtilityCore;
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\FormProtection\FormProtectionFactory;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

class ImportController extends ActionController
{
    
    /**
     * BackendTemplateContainer
     *
     * @var BackendTemplateView
     */
    protected $view;
    
    
    /**
     * @var
     */
    protected $defaultViewObjectName = BackendTemplateView::class;


    /**
     * Set up the doc header properly here
     *
     * @param ViewInterface $view
     * @return void
     */
    protected function initializeView(ViewInterface $view)
    {
        /** @var BackendTemplateView $view */
        parent::initializeView($view);
    }
    
    public function indexAction()
    {

        $this->view->assign($assignedValues, ['flat1', 'flat']);
    }
}

