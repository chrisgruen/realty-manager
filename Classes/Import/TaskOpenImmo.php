<?php
namespace ChrisGruen\RealtyManager\Import;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Scheduler\Task\AbstractTask;

class TaskOpenImmo extends AbstractTask {

    /**
     * @var OpenImmoImport
     */
    protected $importService = null;


    public function execute() {

        set_time_limit(300);
        $this->importService = new OpenImmoImport();

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_realtymanager_domain_model_employer');
        $sql = "SELECT import_folder from tx_realtymanager_domain_model_employer";
        $employer_results = $connection->executeQuery($sql)->fetchAll();

        foreach ($employer_results as $employer_import) {
            if ($employer_import['import_folder'] != '') {
                $employer_folder = $employer_import['import_folder'];
                $importResults = $this->importService->importFromZip($employer_folder);
            }
        }

        return $this->importService->wasSuccessful();
    }
}

