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

}
