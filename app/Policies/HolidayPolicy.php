<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Holiday;
use App\Models\User;

class HolidayPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active && $user->role === UserRole::Administrator;
    }

    public function view(User $user, Holiday $holiday): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Holiday $holiday): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, Holiday $holiday): bool
    {
        return $this->viewAny($user);
    }
}
