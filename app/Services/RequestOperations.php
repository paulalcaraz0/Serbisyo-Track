<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\RequestActivityType;
use App\Enums\UserRole;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Support\PublicRequestMessages;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class RequestOperations
{
    public function __construct(private readonly ResidentUpdateNotifier $notifier) {}

    public function assign(ServiceRequest $serviceRequest, User $actor, ?User $assignee): ServiceRequest
    {
        return DB::transaction(function () use ($serviceRequest, $actor, $assignee): ServiceRequest {
            $locked = ServiceRequest::query()->lockForUpdate()->findOrFail($serviceRequest->id);
            Gate::forUser($actor)->authorize('assign', $locked);

            if ($assignee !== null && (! $assignee->is_active || $assignee->email_verified_at === null)) {
                throw ValidationException::withMessages(['assignee_id' => 'Choose an active, verified staff member.']);
            }

            if ($actor->role !== UserRole::Administrator) {
                $mayClaim = $locked->assigned_to === null && $assignee?->id === $actor->id;
                $mayRelease = $locked->assigned_to === $actor->id && $assignee === null;

                if (! $mayClaim && ! $mayRelease) {
                    abort(403);
                }
            }

            if ($locked->assigned_to === $assignee?->id) {
                throw ValidationException::withMessages(['assignee_id' => 'The request already has that assignment.']);
            }

            $locked->forceFill([
                'assigned_to' => $assignee?->id,
                'assigned_at' => $assignee === null ? null : now(),
            ])->save();

            $locked->activities()->create([
                'actor_id' => $actor->id,
                'subject_user_id' => $assignee?->id,
                'event_type' => RequestActivityType::Assignment,
                'private_details' => $assignee === null ? 'Request unassigned.' : 'Request assigned.',
            ]);

            return $locked->fresh(['service', 'assignee', 'appointment', 'attachments', 'activities.actor', 'activities.subjectUser']);
        });
    }

    public function addInternalNote(ServiceRequest $serviceRequest, User $actor, string $body): ServiceRequest
    {
        return DB::transaction(function () use ($serviceRequest, $actor, $body): ServiceRequest {
            $locked = ServiceRequest::query()->lockForUpdate()->findOrFail($serviceRequest->id);
            Gate::forUser($actor)->authorize('addInternalNote', $locked);

            $locked->activities()->create([
                'actor_id' => $actor->id,
                'event_type' => RequestActivityType::InternalNote,
                'private_details' => trim($body),
            ]);

            $locked->touch();

            return $locked->fresh(['service', 'assignee', 'appointment', 'attachments', 'activities.actor', 'activities.subjectUser']);
        });
    }

    public function updateAppointment(
        ServiceRequest $serviceRequest,
        User $actor,
        AppointmentStatus $status,
        ?CarbonInterface $confirmedStartAt,
        ?string $privateNote,
    ): ServiceRequest {
        $messages = PublicRequestMessages::appointment($status);

        $updated = DB::transaction(function () use ($serviceRequest, $actor, $status, $confirmedStartAt, $privateNote, $messages): ServiceRequest {
            $locked = ServiceRequest::query()->lockForUpdate()->findOrFail($serviceRequest->id);
            Gate::forUser($actor)->authorize('manageAppointment', $locked);
            $appointment = $locked->appointment()->lockForUpdate()->firstOrFail();

            $allowedStatuses = match ($appointment->status) {
                AppointmentStatus::Requested => [AppointmentStatus::Confirmed, AppointmentStatus::RescheduleRequested, AppointmentStatus::Cancelled],
                AppointmentStatus::Confirmed => [AppointmentStatus::Confirmed, AppointmentStatus::RescheduleRequested, AppointmentStatus::Cancelled],
                AppointmentStatus::RescheduleRequested => [AppointmentStatus::Confirmed, AppointmentStatus::Cancelled],
                AppointmentStatus::Cancelled => [],
            };

            if (! in_array($status, $allowedStatuses, true)) {
                throw ValidationException::withMessages(['status' => 'That appointment action is not allowed from the current state.']);
            }

            if ($status === AppointmentStatus::Confirmed && $confirmedStartAt === null) {
                throw ValidationException::withMessages(['confirmed_start_at' => 'Choose the confirmed appointment date and time.']);
            }

            $appointment->forceFill([
                'status' => $status,
                'confirmed_start_at' => $status === AppointmentStatus::Confirmed ? $confirmedStartAt : null,
            ])->save();

            $locked->activities()->create([
                'actor_id' => $actor->id,
                'event_type' => RequestActivityType::Appointment,
                'public_message_en' => $messages['en'],
                'public_message_fil' => $messages['fil'],
                'private_details' => $this->clean($privateNote),
            ]);

            $locked->touch();

            return $locked->fresh(['service', 'assignee', 'appointment', 'attachments', 'activities.actor', 'activities.subjectUser']);
        });

        $this->notifier->send($updated, $messages['en'], $messages['fil']);

        return $updated;
    }

    private function clean(?string $value): ?string
    {
        $clean = trim((string) $value);

        return $clean === '' ? null : $clean;
    }
}
