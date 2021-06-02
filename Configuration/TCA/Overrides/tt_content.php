<?php

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'RealtyManager',
    'Immobilienmanager',
    'Realty Manager',
    'EXT:realty_manager/Resources/Public/Icons/Extension.svg'
);

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['realtymanager_immobilienmanager'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'realtymanager_immobilienmanager',
    'FILE:EXT:realty_manager/Configuration/FlexForms/flexform_realty.xml'
    );
