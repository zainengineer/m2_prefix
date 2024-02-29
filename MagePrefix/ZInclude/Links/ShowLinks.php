<?php

namespace MagePrefix\ZInclude\Links;

use MagePrefix\ZInclude\Traits\SingletonTrait;
use RecursiveDirectoryIterator;
use RecursiveTreeIterator;

class ShowLinks
{
    use SingletonTrait;

    public function __construct(
        protected ListSnippets $listSnippets,
    )
    {
    }


    protected function showFiles(string $title, string $folder, bool $showRuler, string $prefix='')
    {
        $hr = $showRuler ? '<hr/><br/>' : '';
        echo "{$hr}$title<br/><br/>";
        $targetPath = ZINCLUDE_BASE_DIR . "/$folder";
        echo $this->listSnippets->getAll($targetPath,$prefix);
    }
    function showAllTypsOfSnippets()
    {
        $this->showFiles('project snippets','snippets',false);
        $this->showFiles('non-magento snippets','snippets_core_pre',true);
        $this->showFiles('built-ins','builtin',true,'builtin/');
        !d(1);
    }
}
