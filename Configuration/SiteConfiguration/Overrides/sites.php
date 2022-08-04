<?php
// Experimental example to add a new field to the site configuration

// Configure a new simple required input field to site
$GLOBALS['SiteConfiguration']['site_errorhandling']['columns'] += [
    'protectedInfoUid' => [
        'label' => 'Page id with information about protection',
        'description' => 'my desc 1',
        'config' => [
            'type' => 'input',
            'eval' => 'required',
            'renderType' => 'inputLink',
            'fieldControl' => [
                'linkPopup' => [
                    'options' => [
                        'blindLinkOptions' => 'file,telephone,mail,spec,folder',
                    ]
                ],
            ],
        ],
    ],
    'loginPageUid' => [
        'label' => 'Page id of login page',
        'description' => 'my desc 2',
        'config' => [
            'type' => 'input',
            'eval' => 'required',
            'renderType' => 'inputLink',
            'fieldControl' => [
                'linkPopup' => [
                    'options' => [
                        'blindLinkOptions' => 'file,telephone,mail,spec,folder',
                    ]
                ],
            ],
        ],
    ],
];

// And add it to showitem
if (isset($GLOBALS['SiteConfiguration']['site_errorhandling']['types']['Page']['showitem'])) {
    $GLOBALS['SiteConfiguration']['site_errorhandling']['types']['Page']['showitem'] .= ', protectedInfoUid, loginPageUid';
}
