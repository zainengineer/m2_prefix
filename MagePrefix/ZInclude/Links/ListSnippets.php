<?php

namespace MagePrefix\ZInclude\Links;

use MagePrefix\ZInclude\Traits\SingletonTrait;
use RecursiveDirectoryIterator;
use RecursiveTreeIterator;

class ListSnippets
{
    use SingletonTrait;

    public function __construct()
    {
    }

    function getAll($snippetsPath, $prefix = '')
    {
        $aPhpPath = $this->phpPaths($snippetsPath, $prefix);
        $aLinks = $this->pathsToLink($aPhpPath);
        $vLinks = $this->linkToString($aLinks);
        return $vLinks;
    }

    function linkToString($aLinks)
    {
        return implode("\n<br/>", $aLinks);
    }

    function pathsToLink($aPhpPath)
    {
        $aLinks = [];
        foreach ($aPhpPath as $fileName => $opName) {
            $url = "<a href='/?op=$opName'>$opName</a>";
            $aLinks[] = $url;
        }
        return $aLinks;
    }

    protected function canExcludeFile($fileName): bool
    {
        return (str_contains($fileName,'SnippetModels')
        || str_contains($fileName,'util_scripts')
        ) ;
    }

    protected function phpPaths($snippetsPath, $prefix = '')
    {
        $it = new RecursiveTreeIterator(new RecursiveDirectoryIterator($snippetsPath,
            RecursiveDirectoryIterator::SKIP_DOTS + RecursiveDirectoryIterator::UNIX_PATHS));
        $aPhpPath = [];
        foreach ($it as $path) {
            $split = explode($snippetsPath . '/', $path);
            $fileName = $split[1];
            $extension = substr($fileName, -4);
            if ($extension == '.php') {
                $firstFileNameChars = substr(basename($fileName), 0, 2);
                if ($firstFileNameChars == '__') {
                    continue;
                }
                //custom exclude
                if ($this->canExcludeFile($fileName)) {
                    continue;
                }
                $aPhpPath[$fileName] = $prefix . ltrim(pathinfo($fileName, PATHINFO_DIRNAME) . '/' . pathinfo($fileName, PATHINFO_FILENAME), './');
            }
        }
        return $aPhpPath;
    }
}
