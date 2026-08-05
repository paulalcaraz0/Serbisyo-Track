<?php

namespace Tests\Feature\Admin;

use App\Enums\AuditEventType;
use App\Enums\RequestActivityType;
use App\Enums\ServiceRequestStatus;
use App\Enums\UserRole;
use App\Models\AuditEvent;
use App\Models\OfficeSetting;
use App\Models\RequestAttachment;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdministrationAndReportingTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_cannot_access_phase_five_administration_routes(): void
    {
        $staff = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($staff)->get(route('admin.staff.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.staff.create'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.staff.edit', $target))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.reports.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.reports.export'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.audit.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.settings.edit'))->assertForbidden();
    }

    public function test_administrator_can_search_create_and_update_staff_with_audited_changes(): void
    {
        $administrator = User::factory()->create(['role' => UserRole::Administrator]);
        User::factory()->create(['name' => 'Different Person']);

        $this->actingAs($administrator)
            ->post(route('admin.staff.store'), [
                'name' => 'Fictional Operations User',
                'email' => 'operations@example.test',
                'role' => UserRole::Staff->value,
                'password' => 'StrongDemo!2026',
                'password_confirmation' => 'StrongDemo!2026',
            ])
            ->assertRedirect();

        $created = User::query()->where('email', 'operations@example.test')->firstOrFail();
        $this->assertTrue(Hash::check('StrongDemo!2026', $created->password));
        $this->assertNotNull($created->email_verified_at);
        $this->assertDatabaseHas('audit_events', [
            'actor_id' => $administrator->id,
            'action' => AuditEventType::StaffCreated->value,
            'subject_identifier' => (string) $created->id,
        ]);

        $this->actingAs($administrator)
            ->get(route('admin.staff.index', ['search' => 'Operations', 'role' => 'staff', 'status' => 'active']))
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/staff/index')
                ->has('staffAccounts.data', 1)
                ->where('staffAccounts.data.0.email', 'operations@example.test'));

        $this->actingAs($administrator)
            ->put(route('admin.staff.update', $created), [
                'name' => 'Fictional Operations Administrator',
                'email' => 'operations@example.test',
                'role' => UserRole::Administrator->value,
                'is_active' => true,
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertRedirect(route('admin.staff.edit', $created));

        $this->assertSame(UserRole::Administrator, $created->fresh()->role);
        $this->assertDatabaseHas('audit_events', [
            'actor_id' => $administrator->id,
            'action' => AuditEventType::StaffUpdated->value,
            'subject_identifier' => (string) $created->id,
        ]);
    }

    public function test_administrator_cannot_deactivate_or_demote_self_or_the_last_active_administrator(): void
    {
        $administrator = User::factory()->create(['role' => UserRole::Administrator]);

        $this->actingAs($administrator)
            ->put(route('admin.staff.update', $administrator), $this->staffPayload([
                'name' => $administrator->name,
                'email' => $administrator->email,
                'is_active' => false,
            ]))
            ->assertSessionHasErrors('is_active');

        $this->actingAs($administrator)
            ->put(route('admin.staff.update', $administrator), $this->staffPayload([
                'name' => $administrator->name,
                'email' => $administrator->email,
                'role' => UserRole::Staff->value,
            ]))
            ->assertSessionHasErrors('is_active');

        $administrator->refresh();
        $this->assertTrue($administrator->is_active);
        $this->assertSame(UserRole::Administrator, $administrator->role);
    }

    public function test_deactivating_staff_releases_open_assignments_and_preserves_history(): void
    {
        $administrator = User::factory()->create(['role' => UserRole::Administrator]);
        $staff = User::factory()->create();
        $openRequest = ServiceRequest::factory()->create(['assigned_to' => $staff->id, 'assigned_at' => now()]);
        $closedRequest = ServiceRequest::factory()->create([
            'assigned_to' => $staff->id,
            'status' => ServiceRequestStatus::Completed,
            'closed_at' => now(),
        ]);

        $this->actingAs($administrator)
            ->put(route('admin.staff.update', $staff), $this->staffPayload([
                'name' => $staff->name,
                'email' => $staff->email,
                'is_active' => false,
            ]))
            ->assertRedirect();

        $this->assertFalse($staff->fresh()->is_active);
        $this->assertNull($openRequest->fresh()->assigned_to);
        $this->assertSame($staff->id, $closedRequest->fresh()->assigned_to);
        $this->assertDatabaseHas('request_activities', [
            'service_request_id' => $openRequest->id,
            'actor_id' => $administrator->id,
            'subject_user_id' => $staff->id,
            'event_type' => RequestActivityType::Assignment->value,
        ]);
    }

    public function test_office_settings_are_validated_audited_and_shared_with_public_pages(): void
    {
        $administrator = User::factory()->create(['role' => UserRole::Administrator]);

        $this->actingAs($administrator)
            ->patch(route('admin.settings.update'), [
                'office_name_en' => 'Fictional Community Service Office',
                'office_name_fil' => 'Kathang-isip na Tanggapan ng Serbisyo',
                'contact_email' => 'office@example.test',
                'contact_phone' => '(02) 8999 1111',
                'address_en' => 'Fictional Civic Center',
                'address_fil' => 'Kathang-isip na Sentrong Sibiko',
                'retention_days' => 365,
            ])
            ->assertRedirect(route('admin.settings.edit'));

        $this->assertSame(365, OfficeSetting::current()->retention_days);
        $this->assertDatabaseHas('audit_events', ['action' => AuditEventType::OfficeSettingsUpdated->value]);
        $this->get(route('home'))->assertInertia(fn (Assert $page) => $page
            ->where('office.name', 'Fictional Community Service Office')
            ->where('office.email', 'office@example.test'));

        $this->actingAs($administrator)
            ->patch(route('admin.settings.update'), [
                'office_name_en' => 'Office',
                'office_name_fil' => 'Tanggapan',
                'contact_email' => 'office@example.test',
                'contact_phone' => '123',
                'address_en' => 'Address',
                'address_fil' => 'Tirahan',
                'retention_days' => 10,
            ])
            ->assertSessionHasErrors('retention_days');
    }

    public function test_reports_return_aggregates_without_resident_personal_data(): void
    {
        $administrator = User::factory()->create(['role' => UserRole::Administrator]);
        $service = Service::factory()->create(['name_en' => 'Fictional Report Service']);
        ServiceRequest::factory()->for($service)->create([
            'status' => ServiceRequestStatus::Completed,
            'submitted_at' => now()->subDays(2),
            'closed_at' => now()->subDay(),
            'resident_name' => 'Resident Must Stay Private',
            'request_details' => 'Private report detail.',
        ]);
        ServiceRequest::factory()->for($service)->create([
            'submitted_at' => now()->subDay(),
            'due_at' => now()->subHour(),
        ]);

        $this->actingAs($administrator)
            ->get(route('admin.reports.index', [
                'date_from' => now()->subWeek()->toDateString(),
                'date_to' => now()->toDateString(),
                'service' => $service->slug,
                'status' => 'all',
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/reports/index')
                ->where('analytics.summary.total', 2)
                ->where('analytics.summary.completed', 1)
                ->where('analytics.summary.overdue', 1)
                ->where('analytics.service_breakdown.0.name', 'Fictional Report Service')
                ->missing('requests')
                ->missing('resident_name')
                ->missing('request_details'));
    }

    public function test_csv_export_is_formula_safe_omits_pii_and_creates_a_sanitized_audit_event(): void
    {
        $administrator = User::factory()->create(['role' => UserRole::Administrator]);
        $assignee = User::factory()->create(['name' => '@Spreadsheet Formula']);
        $service = Service::factory()->create(['name_en' => '=DANGEROUS()']);
        $serviceRequest = ServiceRequest::factory()->for($service)->create([
            'assigned_to' => $assignee->id,
            'submitted_at' => now(),
            'resident_name' => 'Private Resident Name',
            'request_details' => 'Private request description',
        ]);

        $response = $this->actingAs($administrator)->get(route('admin.reports.export', [
            'date_from' => now()->toDateString(),
            'date_to' => now()->toDateString(),
            'service' => 'all',
            'status' => 'all',
        ]));

        $response->assertOk()->assertDownload();
        $csv = $response->streamedContent();
        $this->assertStringContainsString($serviceRequest->public_reference, $csv);
        $this->assertStringContainsString("'=DANGEROUS()", $csv);
        $this->assertStringContainsString("'@Spreadsheet Formula", $csv);
        $this->assertStringNotContainsString('Private Resident Name', $csv);
        $this->assertStringNotContainsString('Private request description', $csv);

        $event = AuditEvent::query()->where('action', AuditEventType::ReportExported)->firstOrFail();
        $this->assertSame(1, $event->metadata['row_count']);
        $this->assertArrayNotHasKey('resident_name', $event->metadata);
    }

    public function test_audit_logger_allow_lists_metadata_and_admin_can_filter_history(): void
    {
        $administrator = User::factory()->create(['role' => UserRole::Administrator]);
        app(AuditLogger::class)->record($administrator, AuditEventType::RequestStatusChanged, 'request', 'ST-DEMO-SAFE-1234', [
            'request_reference' => 'ST-DEMO-SAFE-1234',
            'from_status' => 'submitted',
            'to_status' => 'acknowledged',
            'resident_name' => 'Must be discarded',
            'private_note' => 'Must be discarded',
        ]);

        $event = AuditEvent::query()->firstOrFail();
        $this->assertArrayNotHasKey('resident_name', $event->metadata);
        $this->assertArrayNotHasKey('private_note', $event->metadata);

        $this->actingAs($administrator)
            ->get(route('admin.audit.index', ['action' => AuditEventType::RequestStatusChanged->value]))
            ->assertInertia(fn (Assert $page) => $page
                ->component('admin/audit/index')
                ->has('events.data', 1)
                ->where('events.data.0.subject_identifier', 'ST-DEMO-SAFE-1234')
                ->missing('events.data.0.metadata.resident_name')
                ->missing('events.data.0.metadata.private_note'));
    }

    public function test_retention_dry_run_preserves_records_and_actual_cleanup_deletes_only_expired_closed_requests_and_files(): void
    {
        Storage::fake('local');
        OfficeSetting::current()->update(['retention_days' => 30]);
        $expired = ServiceRequest::factory()->create([
            'status' => ServiceRequestStatus::Completed,
            'closed_at' => now()->subDays(31),
        ]);
        $attachment = RequestAttachment::factory()->for($expired)->create(['disk' => 'local']);
        Storage::disk('local')->put($attachment->path, 'expired fictional file');
        $recent = ServiceRequest::factory()->create([
            'status' => ServiceRequestStatus::Completed,
            'closed_at' => now()->subDays(29),
        ]);
        $oldOpen = ServiceRequest::factory()->create([
            'submitted_at' => now()->subDays(100),
            'closed_at' => null,
        ]);

        $this->artisan('requests:purge-expired', ['--dry-run' => true])->assertSuccessful();
        $this->assertDatabaseHas('service_requests', ['id' => $expired->id]);
        Storage::disk('local')->assertExists($attachment->path);

        $this->artisan('requests:purge-expired')->assertSuccessful();
        $this->assertDatabaseMissing('service_requests', ['id' => $expired->id]);
        $this->assertDatabaseHas('service_requests', ['id' => $recent->id]);
        $this->assertDatabaseHas('service_requests', ['id' => $oldOpen->id]);
        Storage::disk('local')->assertMissing($attachment->path);
        $this->assertDatabaseHas('audit_events', ['action' => AuditEventType::RetentionPurged->value]);
    }

    /** @param array<string, mixed> $overrides */
    private function staffPayload(array $overrides = []): array
    {
        return array_replace([
            'name' => 'Staff Account',
            'email' => 'staff-account@example.test',
            'role' => UserRole::Administrator->value,
            'is_active' => true,
            'password' => '',
            'password_confirmation' => '',
        ], $overrides);
    }
}
