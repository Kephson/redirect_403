<?php
// Experimental example to add a new field to the site configuration

// Configure a new simple required input field to site
$GLOBALS['SiteConfiguration']['site_errorhandling']['columns'] += [
    'protectedInfoLink' => [
        'label' => 'Page id with information about protection',
        'description' => 'Select a page with information why the users has no access here',
        'config' => [
            'type' => 'input',
            'eval' => '',
            'renderType' => 'inputLink',
            'fieldControl' => [
                'linkPopup' => [
                    'options' => [
                        'blindLinkOptions' => 'file,telephone,mail,spec,folder,external',
                    ]
                ],
            ],
        ],
    ],
    'loginPageLink' => [
        'label' => 'Page id of login page',
        'description' => 'Select a page where to find the login module',
        'config' => [
            'type' => 'input',
            'eval' => '',
            'renderType' => 'inputLink',
            'fieldControl' => [
                'linkPopup' => [
                    'options' => [
                        'blindLinkOptions' => 'file,telephone,mail,spec,folder,external',
                    ]
                ],
            ],
        ],
    ],
];

// And add it to showitem
if (isset($GLOBALS['SiteConfiguration']['site_errorhandling']['types']['PHP']['showitem'])) {
    $GLOBALS['SiteConfiguration']['site_errorhandling']['types']['PHP']['showitem'] .= ', protectedInfoLink, loginPageLink';
}
