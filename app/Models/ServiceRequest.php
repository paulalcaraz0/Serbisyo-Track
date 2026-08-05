<?php

namespace App\Models;

use App\Enums\ServiceRequestStatus;
use Database\Factories\ServiceRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ServiceRequest extends Model
{
    /** @use HasFactory<ServiceRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'service_id',
        'public_reference',
        'tracking_pin_hash',
        'status',
        'locale',
        'resident_name',
        'contact_email',
        'contact_phone',
        'preferred_contact',
        'general_location',
        'request_details',
        'consented_at',
        'submitted_at',
        'assigned_to',
        'assigned_at',
        'due_at',
        'closed_at',
    ];

    protected $hidden = [
        'tracking_pin_hash',
        'resident_name',
        'contact_email',
        'contact_phone',
        'general_location',
        'request_details',
    ];

    protected $casts = [
        'status' => ServiceRequestStatus::class,
        'resident_name' => 'encrypted',
        'contact_email' => 'encrypted',
        'contact_phone' => 'encrypted',
        'general_location' => 'encrypted',
        'request_details' => 'encrypted',
        'consented_at' => 'datetime',
        'submitted_at' => 'datetime',
        'assigned_at' => 'datetime',
        'due_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'public_reference';
    }

    public static function generateReference(): string
    {
        $alphabet = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ';

        do {
            $random = '';

            for ($index = 0; $index < 12; $index++) {
                $random .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }

            $reference = 'ST-'.implode('-', str_split($random, 4));
        } while (self::query()->where('public_reference', $reference)->exists());

        return $reference;
    }

    /** @return BelongsTo<Service, $this> */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /** @return HasOne<RequestAppointment, $this> */
    public function appointment(): HasOne
    {
        return $this->hasOne(RequestAppointment::class);
    }

    /** @return HasMany<RequestAttachment, $this> */
    public function attachments(): HasMany
    {
        return $this->hasMany(RequestAttachment::class)->orderBy('created_at');
    }

    /** @return BelongsTo<User, $this> */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /** @return HasMany<RequestActivity, $this> */
    public function activities(): HasMany
    {
        return $this->hasMany(RequestActivity::class)->orderBy('created_at')->orderBy('id');
    }
}
