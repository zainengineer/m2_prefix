<?php

namespace MagePrefix\ZInclude;

use MagePrefix\ZInclude\AlwaysLoaded\RequestNotFound;
use MagePrefix\ZInclude\AlwaysLoaded\ZNgrock;

if (!defined('PHP_VERSION_ID') || !(PHP_VERSION_ID === 70002 || PHP_VERSION_ID === 70004 || PHP_VERSION_ID >= 70006)) {
    echo "This Prefix util needs PHP 7 at minimum";
    exit(1);
}

define('ZINCLUDE_BASE_DIR', __DIR__);
require_once __DIR__ . "/MagePrefix/ZInclude/AutoLoad.php";
spl_autoload_register(['\MagePrefix\ZInclude\AutoLoad', 'AutoLoadHandler'], prepend: true);


(new RequestNotFound())->process();

$ngrok  =  ZNgrock::getSingleton();
$ngrok->replaceHostIfNeeded();
if (empty($_GET['op'])) {
    $ngrok->ngrockConfigChangeRequired();
    if (!spl_autoload_unregister(['\MagePrefix\ZInclude\AutoLoad', 'AutoLoadHandler'])) {
        throw new \Exception('Could not unregister handler');
    }
    return;
}

require_once __DIR__ . '/snippet_include.php';
$autoInclude = \AutoInclude::getSingleton();
$autoInclude->processSnippet();
die;
