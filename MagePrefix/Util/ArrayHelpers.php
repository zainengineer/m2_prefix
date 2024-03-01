<?php

namespace MagePrefix\Util;

class ArrayHelpers
{
    public function arrayLookupColumn(
        array  $lookup,
        string $columnName,
        bool   $useUnique
    )
    {
        $columnValues = array_column($lookup,$columnName);
        $columnValues = $useUnique ? array_unique($columnValues) : $columnValues;
        return array_combine($columnValues,$columnValues);
    }
    public function getFilteredLines(
        array  $linesToFilter,
        array  $listToCheckAgainst,
        string $columnToUseForFilter,
        bool   $showMissing
    ): array
    {
        return array_filter($linesToFilter, function ($singleLine, $index)
        use ($listToCheckAgainst, $showMissing, $columnToUseForFilter) {
            if ($index === 0) {
                if (is_numeric(key($singleLine))) {
                    return true;
                }
            }
            if (!isset($singleLine[$columnToUseForFilter])) {
                !d("$columnToUseForFilter missing");
                !d($singleLine);
                die;
            }
            $indexValue = $singleLine[$columnToUseForFilter];
            return $showMissing ? !isset($listToCheckAgainst[$indexValue]) : isset($listToCheckAgainst[$indexValue]);
        }, ARRAY_FILTER_USE_BOTH);
    }
    public function getArrayPreview(array $lines) : array
    {
        !d(count($lines));
        return array_slice($lines, 0, 20);
    }
}
