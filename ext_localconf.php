<?php
defined('TYPO3_MODE') || die('Access denied.');

/***************
 * Add default RTE configuration
 */
$GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['simple_news'] = 'EXT:simple_news/Configuration/RTE/RealtyManager.yaml';

/***************
 * PageTS
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:simple_news/Configuration/TsConfig/Page/All.tsconfig">');

/***************
 * Config Extension
 */
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'SimpleNews',
    'ManageSimpleNews',
    [\ChrisGruen\SimpleNews\Controller\SimpleNewsController::class => 'list, show'],
    // non-cacheable actions
    [\ChrisGruen\SimpleNews\Controller\SimpleNewsController::class => '']
);
