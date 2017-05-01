<?php

use Craft\Craft;

$rootPath = getenv('TRAVIS') ? __DIR__.'/../' : __DIR__.'/../../../../';

// Require Craft unit test bootstrap
require_once $rootPath.'craft/app/tests/bootstrap.php';
require_once CRAFT_APP_PATH.'Info.php';

// Require Craft commerce
require_once $rootPath.'craft/plugins/commerce/CommercePlugin.php';
$plugin = new \Craft\CommercePlugin();
foreach (Craft::app()->plugins->autoloadClasses as $classSuffix) {
    $classSubfolder = mb_strtolower($classSuffix).'s';
    $classes = Craft::app()->plugins->getPluginClasses($plugin, $classSubfolder, $classSuffix, true);
}

// Require autoloader
require_once $rootPath.'vendor/autoload.php';
