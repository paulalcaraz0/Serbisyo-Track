<?php

namespace App\Models;

use App\Enums\RequestActivityType;
use App\Enums\ServiceRequestStatus;
use Database\Factories\RequestActivityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestActivity extends Model
{
    /** @use HasFactory<RequestActivityFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'actor_id',
        'subject_user_id',
        'event_type',
        'from_status',
        'to_status',
        'public_message_en',
        'public_message_fil',
        'private_details',
        'created_at',
    ];

    protected $casts = [
        'event_type' => RequestActivityType::class,
        'from_status' => ServiceRequestStatus::class,
        'to_status' => ServiceRequestStatus::class,
        'public_message_en' => 'encrypted',
        'public_message_fil' => 'encrypted',
        'private_details' => 'encrypted',
        'created_at' => 'datetime',
    ];

    /** @return BelongsTo<ServiceRequest, $this> */
    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /** @return BelongsTo<User, $this> */
    public function subjectUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'subject_user_id');
    }
}
