<?php

namespace App\Support;

class CsvCellSanitizer
{
    public static function sanitize(string|int|float|null $value): string
    {
        $clean = str_replace(["\r", "\n", "\t"], ' ', (string) $value);

        if (preg_match('/^[=+\-@]/u', ltrim($clean)) === 1) {
            return "'".$clean;
        }

        return $clean;
    }
}
