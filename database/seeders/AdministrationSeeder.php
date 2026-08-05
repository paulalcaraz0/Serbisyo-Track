<?php

namespace Database\Seeders;

use App\Enums\AuditEventType;
use App\Enums\UserRole;
use App\Models\AuditEvent;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdministrationSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $administrator = User::query()->where('email', 'admin@serbisyotrack.test')->firstOrFail();
        $staff = User::query()->where('email', 'staff@serbisyotrack.test')->firstOrFail();
        $inactive = User::query()->firstOrNew(['email' => 'inactive.staff@serbisyotrack.test']);
        $inactive->forceFill([
            'name' => 'Inactive Demo Staff',
            'email_verified_at' => now()->subMonths(2),
            'password' => Hash::make('SerbisyoTrack!2026'),
            'role' => UserRole::Staff,
            'is_active' => false,
        ])->save();

        $service = Service::query()->orderBy('id')->firstOrFail();
        $request = ServiceRequest::query()->where('public_reference', 'ST-DEMB-RQST-BBBB')->firstOrFail();

        $this->seedEvent($administrator, AuditEventType::StaffCreated, 'staff', (string) $staff->id, [
            'staff_id' => $staff->id,
            'role' => $staff->role->value,
            'is_active' => true,
        ], now()->subDays(4));
        $this->seedEvent($administrator, AuditEventType::ServiceUpdated, 'service', $service->slug, [
            'service_slug' => $service->slug,
        ], now()->subDays(3));
        $this->seedEvent($staff, AuditEventType::RequestStatusChanged, 'request', $request->public_reference, [
            'request_reference' => $request->public_reference,
            'from_status' => 'submitted',
            'to_status' => 'acknowledged',
        ], now()->subDays(2));
    }

    /** @param array<string, mixed> $metadata */
    private function seedEvent(
        User $actor,
        AuditEventType $action,
        string $subjectType,
        string $subjectIdentifier,
        array $metadata,
        \DateTimeInterface $createdAt,
    ): void {
        AuditEvent::query()->firstOrCreate([
            'actor_id' => $actor->id,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_identifier' => $subjectIdentifier,
        ], [
            'metadata' => $metadata,
            'created_at' => $createdAt,
        ]);
    }
}
