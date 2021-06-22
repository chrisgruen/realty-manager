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

    }
    
    
    public function importAction()
    {
        $importResults = '';
        $success = false;
        /*
        try {
            $importResults = $this->importService->importFromZip();
            $success = $this->importService->wasSuccessful();
        } catch (\Exception $exception) {
            $backTrace = \json_encode(
                $exception->getTrace(),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                );
            $importResults .= $exception->getMessage() . "\n" . $backTrace;
            $success = false;
        }
        */
        $this->view->assign('importResults', $importResults);
        $this->view->assign('importStatus', $success ? 0 : 2);
    }
}

