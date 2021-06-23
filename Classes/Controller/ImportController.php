<?php

namespace ChrisGruen\RealtyManager\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
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
use TYPO3\CMS\Core\Resource\ResourceFactory;
//use Exception;
use TYPO3\CMS\Core\Resource\Exception\FolderDoesNotExistException;
use UnexpectedValueException;
use ChrisGruen\RealtyManager\Configuration\ConfigurationImport;
use ChrisGruen\RealtyManager\Import\OpenImmoImport;

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
     * @var OpenImmoImport
     */
    protected $importService = null;
    
     /**
     * Inject the objectimmo import
     *
     * @param ChrisGruen\RealtyManager\Import\OpenImmoImport $importService
     */
    public function injectImportService(OpenImmoImport $importService)
    {
        $this->importService = $importService;
    }


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
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_realtymanager_domain_model_employer');
        $sql = "SELECT uid, company, openimmo_anid, import_folder from tx_realtymanager_domain_model_employer order by uid";
        $employers = $connection->executeQuery($sql)->fetchAll();
        
        $this->view->assign('employers', $employers);
        $this->view->assign('error', $this->checkCorrectConfiguration());
    }
    
    
    public function importAction()
    {
        $importResults = '';
        $success = false;
        $importResults = $this->importService->importFromZip();
        
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
    
    /**
     * Check for correct configuration
     *
     * @return string
     * @throws Exception
     * @throws \TYPO3\CMS\Core\Resource\Exception\InsufficientFolderAccessPermissionsException
     */
    protected function checkCorrectConfiguration()
    {
        $error = '';
        
        $settings = GeneralUtility::makeInstance(ConfigurationImport::class);
               
        try {
            $storageId = (int)$settings->getStorageUidImporter();
            $path = $settings->getResourceFolderImporter();
            if ($storageId === 0) {
                throw new UnexpectedValueException('import.error.configuration.storageUidImporter');
            }
            if (empty($path)) {
                throw new UnexpectedValueException('import.error.configuration.resourceFolderImporter');
            }
            $storage = $this->getResourceFactory()->getStorageObject($storageId);
            $pathExists = $storage->hasFolder($path);
            if (!$pathExists) {
                throw new FolderDoesNotExistException('Folder does not exist', 1474827988);
            }
        } catch (FolderDoesNotExistException $e) {
            $error = 'import.error.configuration.resourceFolderImporter.notExist';
        } catch (UnexpectedValueException $e) {
            $error = $e->getMessage();
        }
        return $error;
    }
    
    /**
     * @return ResourceFactory
     */
    protected function getResourceFactory(): ResourceFactory
    {
        return GeneralUtility::makeInstance(ResourceFactory::class);
    }
    

    public function getEmployer()
    {
        /*
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_realtymanager_domain_model_employer');
        $sql = "SELECT * from tx_realtymanager_domain_model_employer order by uid";
        
        echo $sql;
        $employers = $connection->executeQuery($sql)->fetchAll();
        */
        
        return '$employers function';
    }
}

