<?php
defined('TYPO3_MODE') || die('Access denied.');

/***************
 * Add default RTE configuration
 */
//$GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['realty_manager'] = 'EXT:realty_manager/Configuration/RTE/RealtyManager.yaml';

/***************
 * PageTS
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:realty_manager/Configuration/TsConfig/Page/All.tsconfig">');

/***************
 * Config Extension
 */
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'RealtyManager',
    'Immobilienmanager',
    [\ChrisGruen\RealtyManager\Controller\RealtyManagerController::class => 'list, form, search, detail, ajaxselectdistrict, ajaxsearch, ajaxsearch'],
    // non-cacheable actions
    [\ChrisGruen\RealtyManager\Controller\RealtyManagerController::class => 'list, search, form']
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\ChrisGruen\RealtyManager\Import\TaskOpenImmo::class] = [
    'extension' => 'RealtyManager',
    'title' => 'Task OpenImmo Import ',
    'description' => 'Trigger OpenImmo import',
];
