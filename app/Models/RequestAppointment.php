<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Database\Factories\RequestAppointmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestAppointment extends Model
{
    /** @use HasFactory<RequestAppointmentFactory> */
    use HasFactory;

    protected $fillable = [
        'preferred_date',
        'preferred_time_window',
        'resident_note',
        'status',
        'confirmed_start_at',
    ];

    protected $casts = [
        'preferred_date' => 'date',
        'resident_note' => 'encrypted',
        'status' => AppointmentStatus::class,
        'confirmed_start_at' => 'datetime',
    ];

    /** @return BelongsTo<ServiceRequest, $this> */
    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }
}
