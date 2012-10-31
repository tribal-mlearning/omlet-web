<?php

require_once __DIR__.'/../app_frontend/bootstrap.php.cache';
require_once __DIR__.'/../app_frontend/AppKernel.php';
//require_once __DIR__.'/../app_frontend/AppCache.php';

use Symfony\Component\HttpFoundation\Request;

$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();
//$kernel = new AppCache($kernel);
$kernel->handle(Request::createFromGlobals())->send();
