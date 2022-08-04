<?php
defined('TYPO3_MODE') || die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('redirect_403', 'Configuration/TypoScript', 'Redirect error 403 to login page');
