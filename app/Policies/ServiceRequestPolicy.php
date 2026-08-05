<?php

namespace App\Policies;

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Models\ServiceRequest;
use App\Models\User;

class ServiceRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->is_active;
    }

    public function assign(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->is_active && ! $serviceRequest->status->isTerminal();
    }

    public function transition(User $user, ServiceRequest $serviceRequest): bool
    {
        return $this->canOperate($user, $serviceRequest);
    }

    public function addInternalNote(User $user, ServiceRequest $serviceRequest): bool
    {
        return $this->canOperate($user, $serviceRequest);
    }

    public function manageAppointment(User $user, ServiceRequest $serviceRequest): bool
    {
        return $serviceRequest->appointment()->where('status', '!=', AppointmentStatus::Cancelled->value)->exists()
            && $this->canOperate($user, $serviceRequest);
    }

    public function downloadAttachment(User $user, ServiceRequest $serviceRequest): bool
    {
        return $this->view($user, $serviceRequest);
    }

    private function canOperate(User $user, ServiceRequest $serviceRequest): bool
    {
        return $user->is_active
            && ($user->role === UserRole::Administrator || $serviceRequest->assigned_to === $user->id)
            && ! $serviceRequest->status->isTerminal();
    }
}
