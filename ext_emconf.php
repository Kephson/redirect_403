<?php /** @noinspection PhpUndefinedVariableInspection */

/* * *************************************************************
 * Extension Manager/Repository config file for ext "redirect_403".
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 * ************************************************************* */

$EM_CONF[$_EXTKEY] = [
    'title' => 'Redirect error 403',
    'description' => 'Redirect error 403 to login page or information page',
    'category' => 'fe',
    'version' => '1.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-10.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'state' => 'stable',
    'uploadfolder' => false,
    'createDirs' => false,
    'clearCacheOnLoad' => false,
    'author' => 'Ephraim Härer',
    'author_email' => 'mail@ephra.im',
    'author_company' => 'private',
    'autoload' => [
        'psr-4' => [
            'EHAERER\\Redirect403\\' => 'Classes'
        ]
    ],
];

