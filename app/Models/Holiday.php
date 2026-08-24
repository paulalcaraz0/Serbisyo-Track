<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = [
        'date',
        'name_en',
        'name_fil',
        'is_recurring',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'is_recurring' => 'boolean',
    ];

    /**
     * Holiday lookup tables for business-day arithmetic.
     *
     * @return array{exact: array<string, true>, recurring: array<string, true>}
     */
    public static function lookup(): array
    {
        $exact = [];
        $recurring = [];

        foreach (self::query()->get(['date', 'is_recurring']) as $holiday) {
            $exact[$holiday->date->format('Y-m-d')] = true;

            if ($holiday->is_recurring) {
                $recurring[$holiday->date->format('m-d')] = true;
            }
        }

        return ['exact' => $exact, 'recurring' => $recurring];
    }

    /**
     * @param  array{exact: array<string, true>, recurring: array<string, true>}  $lookup
     */
    public function matches(CarbonInterface $date, array $lookup): bool
    {
        if (isset($lookup['exact'][$date->format('Y-m-d')])) {
            return true;
        }

        return isset($lookup['recurring'][$date->format('m-d')]);
    }
}
