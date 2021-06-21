<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:realty_manager/Resources/Private/Language/locallang_db.xlf:tx_realty_employer',
        'label' => 'company',
        'default_sortby' => 'company',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'delete' => 'deleted',
        'typeicon_classes' => [
            'default' => 'status-user-frontend'
        ],
        'searchFields' => 'uid,title',
    ],
    'columns' => [
        'first_name' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.first_name',
            'config' => [
                'type' => 'input',
                'size' => 25,
                'eval' => 'trim',
                'max' => 50
            ]
        ],
        'last_name' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.last_name',
            'config' => [
                'type' => 'input',
                'size' => 25,
                'eval' => 'trim',
                'max' => 50
            ]
        ],
        'address' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.address',
            'config' => [
                'type' => 'text',
                'cols' => 20,
                'rows' => 3
            ]
        ],
        'telephone' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.phone',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'size' => 20,
                'max' => 30
            ]
        ],
        'fax' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.fax',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'eval' => 'trim',
                'max' => 30
            ]
        ],
        'email' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.email',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'eval' => 'trim',
                'max' => 255
            ]
        ],
        'zip' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.zip',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'size' => 10,
                'max' => 10
            ]
        ],
        'city' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.city',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'eval' => 'trim',
                'max' => 50
            ]
        ],
        'country' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.country',
            'config' => [
                'type' => 'input',
                'size' => 20,
                'eval' => 'trim',
                'max' => 40
            ]
        ],
        'www' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.www',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'size' => 20,
                'max' => 80
            ]
        ],
        'company' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.company',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'size' => 20,
                'max' => 80
            ]
        ],
        'image' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.image',
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'image',
                [
                    'maxitems' => 6,
                    'minitems'=> 0
                ],
                $GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
            )
        ],
        'disable' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.enabled',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        0 => '',
                        1 => '',
                        'invertStateDisplay' => true,
                    ],
                ],
            ],
        ],
        'pid_be_user' => [
            'exclude' => true, 
            'label' => 'LLL:EXT:realty_manager/Resources/Private/Language/locallang_db.xlf:tx_realty_employer.pid_be_user',
            'config' => [
                'type' => 'input',
                'eval' => 'trim',
                'size' => 10,
                'max' => 10
            ]
        ],
        'description' => [
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.description',
            'config' => [
                'type' => 'text',
                'rows' => 5,
                'cols' => 48
            ]
        ],
        'TSconfig' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:TSconfig',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 10,
                'enableTabulator' => true,
                'fixedFont' => true,
            ],
        ],
        'openimmo_anid' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:realty_manager/Resources/Private/Language/locallang_db.xlf:fe_users.tx_realty_openimmo_anid',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
            ],
        ],
        'tx_realty_maximum_objects' => [
            'exclude' => 0,
            'label' => 'LLL:EXT:realty_manager/Resources/Private/Language/locallang_db.xlf:fe_users.tx_realty_maximum_objects',
            'config' => [
                'type' => 'input',
                'size' => 4,
                'max' => 4,
                'eval' => 'int',
                'range' => [
                    'lower' => 0,
                    'upper' => 9999,
                ],
                'default' => 0,
            ],
        ]
    ],
    'types' => [
        '0' => [
            'showitem' => '
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:fe_users.tabs.personelData,
                    company, --palette--;;2, address, zip, city, country, telephone, fax, email, www, image,
                --div--;LLL:EXT:realty_manager/Resources/Private/Language/locallang_db.xlf:fe_users.tx_realty_tab,
			        openimmo_anid, maximum_objects, pid_be_user,
                --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
                    description, TSconfig,
            ',
        ],
    ],
    'palettes' => [
        '2' => ['showitem' => 'first_name,--linebreak--,middle_name,--linebreak--,last_name']
    ]
];

