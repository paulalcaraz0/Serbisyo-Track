<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\AuditEvent;
use App\Models\User;

class AuditEventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->role === UserRole::Administrator;
    }

    public function view(User $user, AuditEvent $auditEvent): bool
    {
        return $this->viewAny($user);
    }
}
