<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\OfficeSetting;
use App\Models\User;

class OfficeSettingPolicy
{
    public function view(User $user, OfficeSetting $settings): bool
    {
        return $user->is_active && $user->role === UserRole::Administrator;
    }

    public function update(User $user, OfficeSetting $settings): bool
    {
        return $this->view($user, $settings);
    }
}
