<?php

namespace MagePrefix\ZInclude\PrefixUtil;

class ZInc
{
    public static function dInc($depth = 4)
    {
        $vKnitPath = ZINCLUDE_BASE_DIR . "/lib/kint_inc.php";
        require_once $vKnitPath;
        \KintHelper::setMaxDepth($depth);
    }

    public static function ensureXdebug()
    {
        $startTime = microtime(true);
        xdebug_break();
        if ((microtime(true)-$startTime) < 0.5){
            !d('enable xdebug listening');
            die;
        }
    }

    public static function getRootPath()
    {
        $root = dirname(ZINCLUDE_BASE_DIR);
        $folderName = basename($root);
        return ($folderName == 'pub') ? dirname($root) : $root;
    }
}
