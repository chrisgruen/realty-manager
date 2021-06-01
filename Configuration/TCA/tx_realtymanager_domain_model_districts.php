<?php
defined('TYPO3_MODE') or die('Access denied.');

$tca = [
    'ctrl' => [
        'title' => 'LLL:EXT:realty_manager/Resources/Private/Language/locallang_db.xlf:tx_realty_districts',
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'translationSource' => 'l10n_source',
        'default_sortby' => 'ORDER BY title',
        'delete' => 'deleted',
        'hideTable' => true,
        'iconfile' => 'EXT:realty_manager/Resources/Public/Icons/District.gif',
        'searchFields' => 'uid, title',
    ],
    'interface' => [
        'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, title',
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'special' => 'languages',
                'items' => [
                    [
                        'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages',
                        -1,
                        'flags-multiple'
                    ],
                ],
                'default' => 0,
            ]
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l10n_parent',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => 'tx_realtymanager_domain_model_cities',
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 0,
                'default' => 0,
            ],
        ],
        'l10n_source' => [
            'config' => [
                'type' => 'passthrough'
            ]
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
                'default' => ''
            ]
        ],
        'title' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:realty_manager/Resources/Private/Language/locallang_db.xlf:tx_realty_districts.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'required',
            ],
        ],
        'city' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'sys_language_uid; LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:sys_language_uid_formlabel, l10n_parent, l10n_diffsource,
                title'
        ],
    ],
];

return $tca;
