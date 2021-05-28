<?php
/**
 * Extension Manager/Repository config file for ext "chess_manager".
 */
$EM_CONF[$_EXTKEY] = [
    'title' => 'Realty Manager',
    'description' => 'TYPO3 extension from v10 LTS (version before created by Olicer Klee) that provides a plugin that displays realty objects (properties, real estate), including an image gallery for each object.',
    'category' => 'templates',
    'constraints' => [
        'depends' => [
            'typo3' => '10.0.0-10.4.99',
            'bootstrap_package' => '11.0.0-11.0.99',
        ],
        'conflicts' => [
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'ChrisGruen\\RealtyManager\\' => 'Classes',
        ],
    ],
    'state' => 'alpha',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'author' => 'Christian Grünwald',
    'author_email' => 'cg@romonta-schach.de',
    'author_company' => 'ChrisGruen',
    'version' => '4.0.0',
];
