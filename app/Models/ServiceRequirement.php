<?php

namespace App\Models;

use Database\Factories\ServiceRequirementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceRequirement extends Model
{
    /** @use HasFactory<ServiceRequirementFactory> */
    use HasFactory;

    protected $fillable = [
        'name_en',
        'name_fil',
        'details_en',
        'details_fil',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'sort_order' => 'integer',
    ];

    /** @return BelongsTo<Service, $this> */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
