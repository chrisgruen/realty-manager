<?php
defined('TYPO3_MODE') || die();

call_user_func(function()
{
   $extensionKey = 'realty_manager';

   /**
    * Default TypoScript
    */
   \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
      $extensionKey,
      'Configuration/TypoScript',
      'Configuration Typoscript Settings'
   );
});