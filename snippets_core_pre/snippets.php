<?php
$listSnippets = \MagePrefix\ZInclude\Snippets\ListSnippets::getSingleton();


function pathsToLink($aPhpPath)
{
    $aLinks = [];
    foreach ($aPhpPath as $fileName => $opName) {
        $url = "<a href='/?op=$opName'>$opName</a>";
        $aLinks[] = $url;
    }
    return $aLinks;
}

function linkToString($aLinks)
{
    return implode("\n<br/>",$aLinks);
}

echo "project snippets<br/><br/>";
$targetPath = dirname(__DIR__) . '/snippets';
echo $listSnippets->getAll($targetPath);

echo "<hr/><br/>non-magento snippets<br/> <br/>\n";
$targetPath =dirname(__DIR__) . '/snippets_core_pre';
echo $listSnippets->getAll($targetPath);


echo "<hr/><br/>built-ins<br/> <br/>\n";
$targetPath=dirname(__DIR__) . '/builtin';
echo $listSnippets->getAll($targetPath,'builtin/');

d(1);
