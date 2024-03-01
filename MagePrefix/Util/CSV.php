<?php

namespace MagePrefix\Util;

class CSV
{
    public function getMappedCsvLines(
        string $path,
        array $replaceHeader = []): array
    {
        static $cache = [];
        if (!isset($cache[$path])) {
            ini_set('memory_limit', '2G');
            $csvLines = file($path);
            if ($csvLines) {
                $header = str_getcsv($csvLines[0]);
                if ($replaceHeader){
                    $header = $replaceHeader;
                }
                unset($csvLines[0]);
                $cache[$path] = array_map(function ($line) use ($header) {
                    return array_combine($header, str_getcsv($line));
                    //extra [] so return value is re-indexed with 0
                }, $csvLines, []);
            } else {
                $cache[$path] = [];
            }

        }
        return $cache[$path];
    }
    public function writeCsv(
        array  $lines,
        string $path,
    )
    {
        $fp = fopen($path, 'w');

        array_walk($lines, function ($singleRow) use ($fp, $lines) {
            if (!is_array($singleRow)) {
                xdebug_break();
            }
            static $headerPrinted = false;
            if (!$headerPrinted){
                if (!is_numeric(key($singleRow))){
                    fputcsv($fp, array_keys($singleRow));
                }
                $headerPrinted = true;
            }
            fputcsv($fp, $singleRow);
        });
    }

}
