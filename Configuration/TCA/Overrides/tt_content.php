<?php

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'RealtyManager',
    'Immobilienmanager',
    'Realty Manager',
    'EXT:simple_news/Resources/Public/Icons/Real.svg'
);

/*
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['realtymanager_Immobilienmanager'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'realtymanager_Immobilienmanager',
    'FILE:EXT:simple_news/Configuration/FlexForms/flexform_realty.xml'
    );
*/