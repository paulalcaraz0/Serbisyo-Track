<?php

namespace App\Support;

use App\Models\Holiday;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class BusinessDayCalculator
{
    public static function add(CarbonInterface $start, int $businessDays): CarbonImmutable
    {
        $date = CarbonImmutable::instance($start)->startOfDay();
        $remaining = max(1, $businessDays);
        $lookup = Holiday::lookup();

        while ($remaining > 0) {
            $date = $date->addDay();

            if (! $date->isWeekend() && ! self::isHoliday($date, $lookup)) {
                $remaining--;
            }
        }

        return $date->endOfDay();
    }

    /**
     * @param  array{exact: array<string, true>, recurring: array<string, true>}  $lookup
     */
    private static function isHoliday(CarbonImmutable $date, array $lookup): bool
    {
        if (isset($lookup['exact'][$date->format('Y-m-d')])) {
            return true;
        }

        return isset($lookup['recurring'][$date->format('m-d')]);
    }
}
