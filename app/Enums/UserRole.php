<?php

namespace App\Enums;

enum UserRole: string
{
    case Staff = 'staff';
    case Administrator = 'administrator';

    public function label(): string
    {
        return match ($this) {
            self::Staff => 'Staff',
            self::Administrator => 'Administrator',
        };
    }
}
