<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\AuditEventType;
use App\Enums\RequestActivityType;
use App\Enums\ServiceRequestStatus;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Support\BusinessDayCalculator;
use App\Support\PublicRequestMessages;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class RequestWorkflow
{
    public function __construct(
        private readonly ResidentUpdateNotifier $notifier,
        private readonly AuditLogger $auditLogger,
    ) {}

    /** @return array<int, ServiceRequestStatus> */
    public function allowedTransitions(ServiceRequestStatus $from): array
    {
        return match ($from) {
            ServiceRequestStatus::Submitted => [
                ServiceRequestStatus::Acknowledged,
                ServiceRequestStatus::Rejected,
                ServiceRequestStatus::Cancelled,
            ],
            ServiceRequestStatus::Acknowledged => [
                ServiceRequestStatus::NeedsInformation,
                ServiceRequestStatus::Scheduled,
                ServiceRequestStatus::InProgress,
                ServiceRequestStatus::Rejected,
                ServiceRequestStatus::Cancelled,
            ],
            ServiceRequestStatus::NeedsInformation => [
                ServiceRequestStatus::Acknowledged,
                ServiceRequestStatus::InProgress,
                ServiceRequestStatus::Rejected,
                ServiceRequestStatus::Cancelled,
            ],
            ServiceRequestStatus::Scheduled => [
                ServiceRequestStatus::NeedsInformation,
                ServiceRequestStatus::InProgress,
                ServiceRequestStatus::Cancelled,
            ],
            ServiceRequestStatus::InProgress => [
                ServiceRequestStatus::NeedsInformation,
                ServiceRequestStatus::ReadyForRelease,
                ServiceRequestStatus::Completed,
                ServiceRequestStatus::Rejected,
                ServiceRequestStatus::Cancelled,
            ],
            ServiceRequestStatus::ReadyForRelease => [
                ServiceRequestStatus::InProgress,
                ServiceRequestStatus::Completed,
                ServiceRequestStatus::Cancelled,
            ],
            ServiceRequestStatus::Completed, ServiceRequestStatus::Rejected, ServiceRequestStatus::Cancelled => [],
        };
    }

    public function transition(
        ServiceRequest $serviceRequest,
        User $actor,
        ServiceRequestStatus $toStatus,
        ?string $publicMessageEn,
        ?string $publicMessageFil,
        ?string $privateNote,
    ): ServiceRequest {
        $defaults = PublicRequestMessages::status($toStatus);
        $messageEn = $this->clean($publicMessageEn) ?? $defaults['en'];
        $messageFil = $this->clean($publicMessageFil) ?? $defaults['fil'];

        $updated = DB::transaction(function () use ($serviceRequest, $actor, $toStatus, $messageEn, $messageFil, $privateNote): ServiceRequest {
            $locked = ServiceRequest::query()->lockForUpdate()->findOrFail($serviceRequest->id);
            Gate::forUser($actor)->authorize('transition', $locked);

            if (! in_array($toStatus, $this->allowedTransitions($locked->status), true)) {
                throw ValidationException::withMessages([
                    'status' => 'That status transition is not allowed from the current state.',
                ]);
            }

            if ($toStatus === ServiceRequestStatus::Scheduled
                && ($locked->appointment === null || $locked->appointment->status !== AppointmentStatus::Confirmed)) {
                throw ValidationException::withMessages([
                    'status' => 'Confirm the appointment before marking the request as scheduled.',
                ]);
            }

            $fromStatus = $locked->status;
            $locked->forceFill([
                'status' => $toStatus,
                'due_at' => $locked->due_at ?? BusinessDayCalculator::add($locked->submitted_at, $locked->service->target_business_days),
                'closed_at' => $toStatus->isTerminal() ? now() : null,
            ])->save();

            $locked->activities()->create([
                'actor_id' => $actor->id,
                'event_type' => RequestActivityType::StatusChange,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'public_message_en' => $messageEn,
                'public_message_fil' => $messageFil,
                'private_details' => $this->clean($privateNote),
            ]);

            $this->auditLogger->record($actor, AuditEventType::RequestStatusChanged, 'request', $locked->public_reference, [
                'request_reference' => $locked->public_reference,
                'from_status' => $fromStatus->value,
                'to_status' => $toStatus->value,
            ]);

            return $locked->fresh(['service', 'assignee', 'appointment', 'attachments', 'activities.actor', 'activities.subjectUser']);
        });

        $this->notifier->send($updated, $messageEn, $messageFil);

        return $updated;
    }

    private function clean(?string $value): ?string
    {
        $clean = trim((string) $value);

        return $clean === '' ? null : $clean;
    }
}
