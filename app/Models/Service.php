<?php

namespace App\Models;

use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory;

    protected $fillable = [
        'slug',
        'name_en',
        'name_fil',
        'description_en',
        'description_fil',
        'eligibility_en',
        'eligibility_fil',
        'fee_centavos',
        'processing_time_en',
        'processing_time_fil',
        'office_hours_en',
        'office_hours_fil',
        'procedure_steps_en',
        'procedure_steps_fil',
        'appointment_required',
        'contact_email',
        'contact_phone',
        'is_active',
    ];

    protected $casts = [
        'appointment_required' => 'boolean',
        'archived_at' => 'datetime',
        'fee_centavos' => 'integer',
        'is_active' => 'boolean',
        'procedure_steps_en' => 'array',
        'procedure_steps_fil' => 'array',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @param  Builder<Service>  $query
     * @return Builder<Service>
     */
    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('is_active', true)->whereNull('archived_at');
    }

    public function isPubliclyVisible(): bool
    {
        return $this->is_active && $this->archived_at === null;
    }

    /** @return HasMany<ServiceRequirement, $this> */
    public function requirements(): HasMany
    {
        return $this->hasMany(ServiceRequirement::class)->orderBy('sort_order');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return BelongsTo<User, $this> */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
