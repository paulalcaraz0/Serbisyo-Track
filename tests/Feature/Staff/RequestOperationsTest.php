<?php

namespace Tests\Feature\Staff;

use App\Enums\AppointmentStatus;
use App\Enums\RequestActivityType;
use App\Enums\ServiceRequestStatus;
use App\Enums\UserRole;
use App\Models\RequestActivity;
use App\Models\RequestAppointment;
use App\Models\RequestAttachment;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\ResidentRequestUpdated;
use App\Support\BusinessDayCalculator;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RequestOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_request_routes_require_an_active_verified_account(): void
    {
        $serviceRequest = ServiceRequest::factory()->create();

        $this->get(route('staff.requests.index'))->assertRedirect(route('login'));
        $this->get(route('staff.requests.show', $serviceRequest))->assertRedirect(route('login'));

        $inactive = User::factory()->create(['is_active' => false]);
        $this->actingAs($inactive)->get(route('staff.requests.index'))->assertRedirect(route('login'));

        $unverified = User::factory()->unverified()->create();
        $this->actingAs($unverified)->get(route('staff.requests.index'))->assertRedirect(route('verification.notice'));
    }

    public function test_staff_default_queue_contains_their_requests_and_unassigned_work_only(): void
    {
        $staff = User::factory()->create();
        $other = User::factory()->create();
        $mine = ServiceRequest::factory()->create(['assigned_to' => $staff->id]);
        $unassigned = ServiceRequest::factory()->create();
        $others = ServiceRequest::factory()->create(['assigned_to' => $other->id]);

        $this->actingAs($staff)
            ->get(route('staff.requests.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('staff/requests/index')
                ->has('requests.data', 2)
                ->where('filters.assignment', 'mine_and_unassigned')
                ->where('summary.mine', 1)
                ->where('summary.unassigned', 1)
                ->where('requests.data', fn ($items) => collect($items)->pluck('reference')->sort()->values()->all() === collect([
                    $mine->public_reference,
                    $unassigned->public_reference,
                ])->sort()->values()->all())
                ->where('summary.open', 3)
                ->missing('requests.data.0.resident_name')
                ->missing('requests.data.0.request_details'));

        $this->actingAs(User::factory()->create(['role' => UserRole::Administrator]))
            ->get(route('staff.requests.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->has('requests.data', 3)
                ->where('filters.assignment', 'all'));

        $this->assertDatabaseHas('service_requests', ['id' => $others->id]);
    }

    public function test_overdue_filter_and_summary_exclude_closed_requests(): void
    {
        $staff = User::factory()->create();
        $overdue = ServiceRequest::factory()->create(['due_at' => now()->subDay()]);
        ServiceRequest::factory()->create(['due_at' => now()->addDay()]);
        ServiceRequest::factory()->create([
            'status' => ServiceRequestStatus::Completed,
            'due_at' => now()->subDays(2),
            'closed_at' => now()->subDay(),
        ]);

        $this->actingAs($staff)
            ->get(route('staff.requests.index', ['overdue' => 1]))
            ->assertInertia(fn (Assert $page) => $page
                ->has('requests.data', 1)
                ->where('requests.data.0.reference', $overdue->public_reference)
                ->where('summary.overdue', 1));
    }

    public function test_staff_can_claim_and_release_unassigned_request_but_cannot_take_another_assignment(): void
    {
        $staff = User::factory()->create();
        $other = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->create();

        $this->actingAs($staff)
            ->patch(route('staff.requests.assignment', $serviceRequest), ['assignee_id' => (string) $staff->id])
            ->assertRedirect();

        $serviceRequest->refresh();
        $this->assertSame($staff->id, $serviceRequest->assigned_to);
        $this->assertNotNull($serviceRequest->assigned_at);
        $this->assertDatabaseHas('request_activities', [
            'service_request_id' => $serviceRequest->id,
            'actor_id' => $staff->id,
            'subject_user_id' => $staff->id,
            'event_type' => RequestActivityType::Assignment->value,
        ]);

        $this->actingAs($other)
            ->patch(route('staff.requests.assignment', $serviceRequest), ['assignee_id' => $other->id])
            ->assertForbidden();

        $this->actingAs($staff)
            ->patch(route('staff.requests.assignment', $serviceRequest), ['assignee_id' => null])
            ->assertRedirect();

        $this->assertNull($serviceRequest->fresh()->assigned_to);
    }

    public function test_administrator_can_assign_active_verified_staff_and_unassign_request(): void
    {
        $administrator = User::factory()->create(['role' => UserRole::Administrator]);
        $staff = User::factory()->create();
        $inactive = User::factory()->create(['is_active' => false]);
        $serviceRequest = ServiceRequest::factory()->create();

        $this->actingAs($administrator)
            ->patch(route('staff.requests.assignment', $serviceRequest), ['assignee_id' => $staff->id])
            ->assertRedirect();
        $this->assertSame($staff->id, $serviceRequest->fresh()->assigned_to);

        $this->actingAs($administrator)
            ->patch(route('staff.requests.assignment', $serviceRequest), ['assignee_id' => $inactive->id])
            ->assertSessionHasErrors('assignee_id');

        $this->actingAs($administrator)
            ->patch(route('staff.requests.assignment', $serviceRequest), ['assignee_id' => null])
            ->assertRedirect();
        $this->assertNull($serviceRequest->fresh()->assigned_to);
    }

    public function test_assigned_staff_can_follow_valid_transition_and_public_update_is_queued(): void
    {
        Queue::fake();
        $staff = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->create([
            'assigned_to' => $staff->id,
            'preferred_contact' => 'email',
            'contact_email' => 'resident@example.test',
        ]);

        $this->actingAs($staff)
            ->post(route('staff.requests.transitions', $serviceRequest), [
                'status' => ServiceRequestStatus::Acknowledged->value,
                'public_message_en' => '',
                'public_message_fil' => '',
                'private_note' => 'Verified the fictional submission.',
            ])
            ->assertRedirect();

        $serviceRequest->refresh();
        $this->assertSame(ServiceRequestStatus::Acknowledged, $serviceRequest->status);
        $activity = $serviceRequest->activities()->latest('id')->firstOrFail();
        $this->assertSame(RequestActivityType::StatusChange, $activity->event_type);
        $this->assertSame(ServiceRequestStatus::Submitted, $activity->from_status);
        $this->assertSame(ServiceRequestStatus::Acknowledged, $activity->to_status);
        $this->assertSame('Verified the fictional submission.', $activity->private_details);
        $this->assertNotNull($activity->public_message_en);
        $this->assertNotNull($activity->public_message_fil);
        Queue::assertPushed(SendQueuedNotifications::class, fn (SendQueuedNotifications $job) => $job->notification instanceof ResidentRequestUpdated);
    }

    public function test_unassigned_staff_cannot_transition_or_add_internal_note(): void
    {
        $staff = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->create();

        $this->actingAs($staff)
            ->post(route('staff.requests.transitions', $serviceRequest), ['status' => ServiceRequestStatus::Acknowledged->value])
            ->assertForbidden();
        $this->actingAs($staff)
            ->post(route('staff.requests.notes', $serviceRequest), ['body' => 'Private note.'])
            ->assertForbidden();

        $this->assertSame(ServiceRequestStatus::Submitted, $serviceRequest->fresh()->status);
        $this->assertDatabaseCount('request_activities', 0);
    }

    public function test_invalid_status_jump_is_rejected_without_history_or_notification(): void
    {
        Queue::fake();
        $staff = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->create(['assigned_to' => $staff->id]);

        $this->actingAs($staff)
            ->post(route('staff.requests.transitions', $serviceRequest), ['status' => ServiceRequestStatus::Completed->value])
            ->assertSessionHasErrors('status');

        $this->assertSame(ServiceRequestStatus::Submitted, $serviceRequest->fresh()->status);
        $this->assertDatabaseCount('request_activities', 0);
        Queue::assertNothingPushed();
    }

    public function test_needs_information_and_rejection_require_bilingual_public_guidance(): void
    {
        $staff = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->create([
            'assigned_to' => $staff->id,
            'status' => ServiceRequestStatus::Acknowledged,
        ]);

        $this->actingAs($staff)
            ->post(route('staff.requests.transitions', $serviceRequest), [
                'status' => ServiceRequestStatus::NeedsInformation->value,
                'public_message_en' => 'Please add a fictional supporting detail.',
                'public_message_fil' => '',
            ])
            ->assertSessionHasErrors(['public_message_fil']);

        $this->assertSame(ServiceRequestStatus::Acknowledged, $serviceRequest->fresh()->status);
    }

    public function test_internal_note_is_encrypted_and_never_exposed_by_public_tracking(): void
    {
        $staff = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->create(['assigned_to' => $staff->id]);

        $this->actingAs($staff)
            ->post(route('staff.requests.notes', $serviceRequest), ['body' => 'Private fictional verification detail.'])
            ->assertRedirect();

        $activity = RequestActivity::query()->firstOrFail();
        $this->assertSame('Private fictional verification detail.', $activity->private_details);
        $raw = DB::table('request_activities')->where('id', $activity->id)->first();
        $this->assertNotSame('Private fictional verification detail.', $raw->private_details);

        $this->withSession([
            'resident_tracking_grants' => [$serviceRequest->public_reference => now()->addMinutes(5)->timestamp],
        ])->get(route('tracking.show', ['reference' => $serviceRequest->public_reference]))
            ->assertInertia(fn (Assert $page) => $page
                ->has('trackedRequest.history', 0)
                ->missing('trackedRequest.activities')
                ->missing('trackedRequest.private_details'));
    }

    public function test_public_tracking_contains_only_allow_listed_bilingual_public_history(): void
    {
        $serviceRequest = ServiceRequest::factory()->create();
        RequestActivity::factory()->for($serviceRequest)->create();
        $staff = User::factory()->create();
        $serviceRequest->activities()->create([
            'actor_id' => $staff->id,
            'event_type' => RequestActivityType::StatusChange,
            'from_status' => ServiceRequestStatus::Submitted,
            'to_status' => ServiceRequestStatus::Acknowledged,
            'public_message_en' => 'English public update.',
            'public_message_fil' => 'Filipino public update.',
            'private_details' => 'Private staff-only detail.',
        ]);

        $this->withSession([
            'locale' => 'fil',
            'resident_tracking_grants' => [$serviceRequest->public_reference => now()->addMinutes(5)->timestamp],
        ])->get(route('tracking.show', ['reference' => $serviceRequest->public_reference]))
            ->assertInertia(fn (Assert $page) => $page
                ->has('trackedRequest.history', 2)
                ->where('trackedRequest.history.1.message', 'Filipino public update.')
                ->where('trackedRequest.history.1.status', 'acknowledged')
                ->missing('trackedRequest.history.1.actor')
                ->missing('trackedRequest.history.1.private_details')
                ->missing('trackedRequest.history.1.public_message_en'));
    }

    public function test_assigned_staff_can_confirm_appointment_and_update_is_queued(): void
    {
        Queue::fake();
        $staff = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->create(['assigned_to' => $staff->id]);
        $appointment = RequestAppointment::factory()->for($serviceRequest)->create();
        $confirmedAt = now()->addWeek()->setTime(10, 0);

        $this->actingAs($staff)
            ->patch(route('staff.requests.appointment', $serviceRequest), [
                'status' => AppointmentStatus::Confirmed->value,
                'confirmed_start_at' => $confirmedAt->toDateTimeString(),
                'private_note' => 'Fictional calendar slot reserved.',
            ])
            ->assertRedirect();

        $appointment->refresh();
        $this->assertSame(AppointmentStatus::Confirmed, $appointment->status);
        $this->assertSame($confirmedAt->format('Y-m-d H:i'), $appointment->confirmed_start_at?->format('Y-m-d H:i'));
        $this->assertDatabaseHas('request_activities', [
            'service_request_id' => $serviceRequest->id,
            'event_type' => RequestActivityType::Appointment->value,
        ]);
        Queue::assertPushed(SendQueuedNotifications::class);
    }

    public function test_request_cannot_be_scheduled_until_appointment_is_confirmed(): void
    {
        Queue::fake();
        $staff = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->create([
            'assigned_to' => $staff->id,
            'status' => ServiceRequestStatus::Acknowledged,
        ]);
        RequestAppointment::factory()->for($serviceRequest)->create();

        $this->actingAs($staff)
            ->post(route('staff.requests.transitions', $serviceRequest), ['status' => ServiceRequestStatus::Scheduled->value])
            ->assertSessionHasErrors('status');

        $this->actingAs($staff)
            ->patch(route('staff.requests.appointment', $serviceRequest), [
                'status' => AppointmentStatus::Confirmed->value,
                'confirmed_start_at' => now()->addWeek()->toDateTimeString(),
            ])
            ->assertRedirect();

        $this->actingAs($staff)
            ->post(route('staff.requests.transitions', $serviceRequest), ['status' => ServiceRequestStatus::Scheduled->value])
            ->assertRedirect();

        $this->assertSame(ServiceRequestStatus::Scheduled, $serviceRequest->fresh()->status);
    }

    public function test_terminal_transition_records_closed_time_and_disables_operations(): void
    {
        Queue::fake();
        $staff = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->create([
            'assigned_to' => $staff->id,
            'status' => ServiceRequestStatus::InProgress,
        ]);

        $this->actingAs($staff)
            ->post(route('staff.requests.transitions', $serviceRequest), ['status' => ServiceRequestStatus::Completed->value])
            ->assertRedirect();

        $serviceRequest->refresh();
        $this->assertSame(ServiceRequestStatus::Completed, $serviceRequest->status);
        $this->assertNotNull($serviceRequest->closed_at);
        $this->assertFalse($staff->can('transition', $serviceRequest));
        $this->assertFalse($staff->can('addInternalNote', $serviceRequest));
    }

    public function test_phone_preference_does_not_queue_email_notification(): void
    {
        Queue::fake();
        $administrator = User::factory()->create(['role' => UserRole::Administrator]);
        $serviceRequest = ServiceRequest::factory()->create([
            'preferred_contact' => 'phone',
            'contact_email' => 'optional@example.test',
        ]);

        $this->actingAs($administrator)
            ->post(route('staff.requests.transitions', $serviceRequest), ['status' => ServiceRequestStatus::Acknowledged->value])
            ->assertRedirect();

        Queue::assertNothingPushed();
    }

    public function test_terminal_requests_cannot_be_reassigned_or_changed(): void
    {
        $administrator = User::factory()->create(['role' => UserRole::Administrator]);
        $serviceRequest = ServiceRequest::factory()->create([
            'status' => ServiceRequestStatus::Completed,
            'closed_at' => now(),
        ]);

        $this->actingAs($administrator)
            ->patch(route('staff.requests.assignment', $serviceRequest), ['assignee_id' => $administrator->id])
            ->assertForbidden();
        $this->actingAs($administrator)
            ->post(route('staff.requests.notes', $serviceRequest), ['body' => 'Should not be accepted.'])
            ->assertForbidden();
    }

    public function test_staff_attachment_download_is_authorized_and_scoped_to_request(): void
    {
        Storage::fake('local');
        $staff = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->create();
        $attachment = RequestAttachment::factory()->for($serviceRequest)->create();
        Storage::disk('local')->put($attachment->path, 'fictional attachment');
        $url = route('staff.requests.attachments.show', [
            'serviceRequest' => $serviceRequest,
            'attachment' => $attachment->public_id,
        ]);

        $this->get($url)->assertRedirect(route('login'));
        $this->actingAs($staff)->get($url)->assertOk()->assertDownload('fictional-document.pdf');

        $other = RequestAttachment::factory()->create();
        $this->actingAs($staff)->get(route('staff.requests.attachments.show', [
            'serviceRequest' => $serviceRequest,
            'attachment' => $other->public_id,
        ]))->assertNotFound();
    }

    public function test_staff_workspace_exposes_operations_data_without_pin_hash_or_storage_path(): void
    {
        $staff = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->create(['assigned_to' => $staff->id]);
        RequestActivity::factory()->for($serviceRequest)->create(['actor_id' => $staff->id]);
        RequestAttachment::factory()->for($serviceRequest)->create();

        $this->actingAs($staff)
            ->get(route('staff.requests.show', $serviceRequest))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('staff/requests/show')
                ->where('requestRecord.data.reference', $serviceRequest->public_reference)
                ->where('requestRecord.data.permissions.transition', true)
                ->has('requestRecord.data.activities', 1)
                ->has('requestRecord.data.attachments', 1)
                ->missing('requestRecord.data.tracking_pin_hash')
                ->missing('requestRecord.data.attachments.0.path')
                ->missing('requestRecord.data.attachments.0.disk'));
    }

    public function test_business_day_target_skips_weekends(): void
    {
        $friday = CarbonImmutable::parse('2026-08-07 09:00:00', 'Asia/Manila');

        $this->assertSame('2026-08-10 23:59:59', BusinessDayCalculator::add($friday, 1)->format('Y-m-d H:i:s'));
        $this->assertSame('2026-08-12 23:59:59', BusinessDayCalculator::add($friday, 3)->format('Y-m-d H:i:s'));
    }
}
