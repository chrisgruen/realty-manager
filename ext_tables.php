<?php
defined('TYPO3_MODE') || die('Access denied.');

// Module System > Backend Users
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
    'RealtyManager',
    'web',
    'Immobilienmanager',
    'bottom',
    [
        \ChrisGruen\RealtyManager\Controller\ImportController::class => 'index, import',
    ],
    [
        'access' => 'user,group',
        'icon' => 'EXT:realty_manager/Resources/Public/Icons/BackEndModule.gif',
        'labels' => 'LLL:EXT:realty_manager/Resources/Private/Language/locallang_mod.xlf',
        // hide the page tree
        //'navigationComponentId' => '',
        'navigationComponentId' => 'TYPO3/CMS/Backend/PageTree/PageTreeElement',
        'inheritNavigationComponentFromMainModule' => false,
    ]
);