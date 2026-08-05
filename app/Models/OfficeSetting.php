<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficeSetting extends Model
{
    protected $fillable = [
        'office_name_en',
        'office_name_fil',
        'contact_email',
        'contact_phone',
        'address_en',
        'address_fil',
        'retention_days',
    ];

    protected $casts = [
        'retention_days' => 'integer',
    ];

    public static function current(): self
    {
        return self::query()->findOrFail(1);
    }
}
