<?php

$rootPath = getenv('TRAVIS') ? __DIR__.'/../' : __DIR__.'/../../../../';

// Require Craft unit test bootstrap
require_once $rootPath.'craft/app/tests/bootstrap.php';
require_once CRAFT_APP_PATH.'Info.php';

// Require Craft commerce
require_once $rootPath.'craft/plugins/commerce/models/Commerce_ProductTypeModel.php';
require_once $rootPath.'craft/plugins/commerce/models/Commerce_ProductTypeLocaleModel.php';
require_once $rootPath.'craft/plugins/commerce/services/Commerce_ProductTypesService.php';

// Require autoloader
require_once $rootPath.'vendor/autoload.php';
