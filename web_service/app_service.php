<?php

require_once __DIR__.'/../app_service/bootstrap.php.cache';
require_once __DIR__.'/../app_service/AppKernel.php';
//require_once __DIR__.'/../app_service/AppCache.php';

use Symfony\Component\HttpFoundation\Request;

$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();
//$kernel = new AppCache($kernel);
$kernel->handle(Request::createFromGlobals())->send();
