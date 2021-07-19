<?php

namespace ChrisGruen\RealtyManager\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Backend\View\BackendTemplateView;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
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
        $dataEmployers = $connection->executeQuery($sql)->fetchAll();

        $employers = $this->selectEmployers($dataEmployers);

        $this->view->assign('employers', $employers);
        $this->view->assign('error', $this->checkCorrectConfiguration());
    }


    public function importAction()
    {
        $errors = '';
        $importResults = '';
        $employer_folder = '';

        $employer_uid = $this->request->hasArgument('employer') ? $this->request->getArgument('employer') : 0;

        //$success = false;
        set_time_limit(300);

        if($employer_uid > 0) {
            $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_realtymanager_domain_model_employer');
            $sql = "SELECT import_folder from tx_realtymanager_domain_model_employer where uid = $employer_uid";
            $employer_result = $connection->executeQuery($sql)->fetch();
            $employer_folder = $employer_result['import_folder'];
        }

        $errors = $this->checkFolderEmployer($employer_folder);

        if ($errors != '') {
            $this->view->assign('error', $this->checkFolderEmployer($employer_folder));
        } else {
            
            $this->importService = new OpenImmoImport();
            
            try {
                $importResults = $this->importService->importFromZip($employer_folder);
                $success = $this->importService->wasSuccessful();
            } catch (\Exception $exception) {
                $backTrace = \json_encode(
                    $exception->getTrace(),
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                    );
                $importResults .= $exception->getMessage() . "\n" . $backTrace;
                $success = false;
            }

            $this->view->assign('importResults', $importResults);
            $this->view->assign('importStatus', $success ? 0 : 2);
        }
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
     * Check for folder Employer
     *
     * @return string
     * @throws Exception
     * @throws \TYPO3\CMS\Core\Resource\Exception\InsufficientFolderAccessPermissionsException
     */
    protected function checkFolderEmployer($folder)
    {
        $error = '';
        $settings = GeneralUtility::makeInstance(ConfigurationImport::class);
        $base_import_folder = $settings->getResourceFolderImporter();
        $base_export_folder = $settings->getResourceFolderExporter();
        $error = '';
        $base_path = realpath(__DIR__ . '/../../../../../');
        $path_import = $base_path.'/fileadmin'.$base_import_folder.'/'.$folder;
        $path_export = $base_path.'/fileadmin'.$base_export_folder.'/'.$folder;

        /* Check Folder for Import Export */
        try {
            if ($folder == '') {
                $folder = "NOT_SET";
                $message = 'Ordner für Import/Export: ';
                throw new FolderDoesNotExistException('Folder does not exist', 1474827988);
            } else if (!is_dir($path_import)) {
                $message = 'Import-Ordner für Zip-Import: ';
                throw new FolderDoesNotExistException('Folder does not exist', 1474827988);
            } else if (!is_dir($path_export)) {
                $message = 'Export-Ordner für Bilder, PDFs: ';
                throw new FolderDoesNotExistException('Folder does not exist', 1474827988);
            } else {

            }
        } catch (FolderDoesNotExistException $e) {
            $error = $message.$base_import_folder.'/'.$folder. ' existiert nicht!';
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


    /**
     * prepare employers for select box
     *
     * @return array
     */
    public function selectEmployers($dataEmployers) {
        $employers = [];
        $city = new \stdClass();

        foreach($dataEmployers as $dataEmployer) {
            $employer = new \stdClass();
            $employer->key = $dataEmployer['uid'];
            $employer->value = $dataEmployer['company'];
            $employers[] = $employer;
        }
        return $employers;
    }
}