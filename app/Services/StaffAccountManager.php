<?php

namespace App\Services;

use App\Enums\AuditEventType;
use App\Enums\RequestActivityType;
use App\Enums\UserRole;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StaffAccountManager
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    /** @param array<string, mixed> $data */
    public function create(User $actor, array $data): User
    {
        return DB::transaction(function () use ($actor, $data): User {
            $staff = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => UserRole::from((string) $data['role']),
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            $this->auditLogger->record($actor, AuditEventType::StaffCreated, 'staff', $staff->id, [
                'staff_id' => $staff->id,
                'role' => $staff->role->value,
                'is_active' => true,
            ]);

            return $staff;
        });
    }

    /** @param array<string, mixed> $data */
    public function update(User $actor, User $staff, array $data): User
    {
        return DB::transaction(function () use ($actor, $staff, $data): User {
            $locked = User::query()->lockForUpdate()->findOrFail($staff->id);
            $newRole = UserRole::from((string) $data['role']);
            $newActive = (bool) $data['is_active'];

            if ($locked->id === $actor->id && (! $newActive || $newRole !== UserRole::Administrator)) {
                throw ValidationException::withMessages([
                    'is_active' => 'You cannot deactivate or remove the administrator role from your own account.',
                ]);
            }

            if ($locked->role === UserRole::Administrator
                && $locked->is_active
                && (! $newActive || $newRole !== UserRole::Administrator)
                && User::query()->whereKeyNot($locked->id)->where('role', UserRole::Administrator)->where('is_active', true)->count() === 0) {
                throw ValidationException::withMessages([
                    'role' => 'At least one active administrator must remain.',
                ]);
            }

            $wasActive = $locked->is_active;
            $locked->forceFill([
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => $newRole,
                'is_active' => $newActive,
            ]);

            if (isset($data['password']) && is_string($data['password']) && $data['password'] !== '') {
                $locked->password = $data['password'];
            }

            $locked->save();
            $released = 0;

            if ($wasActive && ! $newActive) {
                $requests = ServiceRequest::query()
                    ->where('assigned_to', $locked->id)
                    ->whereNull('closed_at')
                    ->lockForUpdate()
                    ->get();

                foreach ($requests as $request) {
                    $request->forceFill(['assigned_to' => null, 'assigned_at' => null])->save();
                    $request->activities()->create([
                        'actor_id' => $actor->id,
                        'subject_user_id' => $locked->id,
                        'event_type' => RequestActivityType::Assignment,
                        'private_details' => 'Assignment released because the staff account was deactivated.',
                    ]);
                    $released++;
                }
            }

            $this->auditLogger->record($actor, AuditEventType::StaffUpdated, 'staff', $locked->id, [
                'staff_id' => $locked->id,
                'role' => $locked->role->value,
                'is_active' => $locked->is_active,
                'assignments_released' => $released,
            ]);

            return $locked->fresh();
        });
    }
}
