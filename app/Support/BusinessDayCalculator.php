<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class BusinessDayCalculator
{
    public static function add(CarbonInterface $start, int $businessDays): CarbonImmutable
    {
        $date = CarbonImmutable::instance($start)->startOfDay();
        $remaining = max(1, $businessDays);

        while ($remaining > 0) {
            $date = $date->addDay();

            if (! $date->isWeekend()) {
                $remaining--;
            }
        }

        return $date->endOfDay();
    }
}
