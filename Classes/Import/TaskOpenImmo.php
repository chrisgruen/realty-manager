<?php
namespace ChrisGruen\RealtyManager\Import;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\Exception\FolderDoesNotExistException;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Scheduler\Task\AbstractTask;
use ChrisGruen\RealtyManager\Configuration\ConfigurationImport;

class TaskOpenImmo extends AbstractTask {

    /**
     * @var OpenImmoImport
     */
    protected $importService = null;


    public function execute() {

        set_time_limit(1000);
        
        $url = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != "off") ? "https" : "http");
        $url .= "://".$_SERVER['HTTP_HOST'];
        $url .= str_replace(basename($_SERVER['SCRIPT_NAME']),"",$_SERVER['SCRIPT_NAME']);
        
        $parseUrl = parse_url($url);        
        $baseUrl = $parseUrl['scheme'].'://'.$parseUrl['host'];
        
        $this->importService = new OpenImmoImport($baseUrl);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_realtymanager_domain_model_employer');
        $sql = "SELECT import_folder from tx_realtymanager_domain_model_employer";
        $employer_results = $connection->executeQuery($sql)->fetchAll();

        foreach ($employer_results as $employer_import) {
            if ($employer_import['import_folder'] != '') {
                $employer_folder = $employer_import['import_folder'];
                if ($this->checkFolderEmployer($employer_folder) == true) {
                    $importResults = $this->importService->importFromZip($employer_folder);
                }
            }
        }

        return $this->importService->wasSuccessful();
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

        if ($folder == '') {
            $folder = "NOT_SET";
            return false;
        } else if (!is_dir($path_import)) {
            return false;
        } else if (!is_dir($path_export)) {
            return false;
        } else {

        }

        return true;
    }

    /**
     * @return ResourceFactory
     */
    protected function getResourceFactory(): ResourceFactory
    {
        return GeneralUtility::makeInstance(ResourceFactory::class);
    }
}

