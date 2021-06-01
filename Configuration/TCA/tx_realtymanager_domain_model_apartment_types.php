<?php
defined('TYPO3_MODE') or die('Access denied.');

return [
    'ctrl' => [
        'title' => 'LLL:EXT:realty_manager/Resources/Private/Language/locallang_db.xlf:tx_realty_apartment_types',
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
        'iconfile' => 'EXT:realty_manager/Resources/Public/Icons/ApartmentType.gif',
        'searchFields' => 'uid,title',
    ],
    'interface' => [
        'showRecordFieldList' => 'sys_language_uid,l10n_parent,l10n_diffsource,title',
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => 1,
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
                'allowed' => 'tx_realtymanager_domain_model_apartment_types',
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
            'label' => 'LLL:EXT:realty_manager/Resources/Private/Language/locallang_db.xlf:tx_realty_apartment_types.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'required',
            ],
        ],
    ],
    'types' => [
        '0' => ['showitem' => 'sys_language_uid; LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:sys_language_uid_formlabel, l10n_parent, l10n_diffsource, 
                title'
               ],
    ],
];