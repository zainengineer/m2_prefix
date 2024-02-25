<?php

namespace MagePrefix\Util;

class CSVLegacy
{
    public function getCsvLines(string $path): array
    {
        static $cache = [];
        if (!isset($cache[$path])) {
            $csvLines = file($path);
            $cache[$path] = array_map(function ($line) {
                return str_getcsv($line);
            }, $csvLines);
        }
        return $cache[$path];
    }
}
