<?php

namespace MagePrefix\ZInclude;
class AutoLoad
{
    public static function AutoLoadHandler(string $class)
    {
        if (strpos($class, 'MagePrefix') === 0) {
            $classPath = str_replace('\\','/',$class);
            $path =  ZINCLUDE_BASE_DIR . "/$classPath.php";
            require_once $path;
        }
        if (strpos($class, 'SnippetModels') === 0) {
            $classPath = str_replace('\\','/',$class);
            $path =  ZINCLUDE_BASE_DIR . "/snippets/$classPath.php";
            require_once $path;
        }

    }
}
