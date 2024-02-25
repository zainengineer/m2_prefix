<?php

namespace MagePrefix\ZInclude\Snippets;
use MagePrefix\ZInclude\Traits\SingletonTrait;
use RecursiveDirectoryIterator;
use RecursiveTreeIterator;

class ListSnippets
{
    use SingletonTrait;

    function getAll($snippetsPath, $prefix = '')
    {
        $aPhpPath = $this->phpPaths($snippetsPath, $prefix);
        $aLinks = pathsToLink($aPhpPath);
        $vLinks = linkToString($aLinks);
        return $vLinks;
    }

    protected function canExcludeFile($fileName) : bool
    {
        return (strpos($fileName,'SnippetModels')===0);
    }
    protected function phpPaths($snippetsPath,$prefix='')
    {
        $it = new RecursiveTreeIterator(new RecursiveDirectoryIterator($snippetsPath,
            RecursiveDirectoryIterator::SKIP_DOTS + RecursiveDirectoryIterator::UNIX_PATHS));
        $aPhpPath = [];
        foreach ($it as $path) {
            $split = explode($snippetsPath . '/', $path);
            $fileName = $split[1];
            $extension = substr($fileName, -4);
            if ($extension == '.php') {
                $firstFileNameChars = substr(basename($fileName),0,2);
                if ($firstFileNameChars=='__'){
                    continue;
                }
                //custom exclude
                if ($this->canExcludeFile($fileName)){
                    continue;
                }
                $aPhpPath[$fileName] = $prefix . ltrim(pathinfo($fileName, PATHINFO_DIRNAME) . '/' . pathinfo($fileName, PATHINFO_FILENAME),'./');
            }
        }
        return $aPhpPath;
    }
}
