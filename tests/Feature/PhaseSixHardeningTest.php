<?php

namespace Tests\Feature;

use App\Enums\ServiceRequestStatus;
use App\Enums\UserRole;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;
use RuntimeException;
use Tests\TestCase;

class PhaseSixHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('web')->get('/_phase6/status/{status}', static function (string $status) {
            abort((int) $status);
        });

        Route::middleware('web')->get('/_phase6/failure', static function (): never {
            throw new RuntimeException('Private exception detail that must never reach the browser.');
        });
    }

    public function test_safe_accessible_error_pages_cover_expected_web_failures(): void
    {
        foreach ([403, 419, 429, 503] as $status) {
            $this->get("/_phase6/status/{$status}")
                ->assertStatus($status)
                ->assertHeader('Cache-Control', 'no-store, private')
                ->assertHeader('Referrer-Policy', 'no-referrer')
                ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')
                ->assertInertia(fn (Assert $page) => $page
                    ->component('errors/show')
                    ->where('status', $status)
                    ->has("copy.statuses.{$status}.title")
                    ->missing('exception'));
        }
    }

    public function test_missing_routes_use_the_safe_error_page(): void
    {
        $this->get('/a-page-that-does-not-exist')
            ->assertNotFound()
            ->assertInertia(fn (Assert $page) => $page
                ->component('errors/show')
                ->where('status', 404)
                ->where('copy.statuses.404.title', 'We could not find that page'));
    }

    public function test_error_pages_follow_the_selected_locale_on_web_routes(): void
    {
        $this->withSession(['locale' => 'fil'])
            ->get('/_phase6/status/403')
            ->assertForbidden()
            ->assertInertia(fn (Assert $page) => $page
                ->where('copy.statuses.403.title', 'Wala kang access sa pahinang ito'));
    }

    public function test_production_error_page_does_not_expose_exception_details(): void
    {
        config(['app.debug' => false]);

        $this->get('/_phase6/failure')
            ->assertInternalServerError()
            ->assertDontSee('Private exception detail')
            ->assertInertia(fn (Assert $page) => $page
                ->component('errors/show')
                ->where('status', 500)
                ->missing('exception'));
    }

    public function test_json_clients_keep_a_machine_readable_error_response(): void
    {
        config(['app.debug' => false]);

        $this->withHeader('Accept', 'application/json')
            ->get('/_phase6/status/404')
            ->assertNotFound()
            ->assertExactJson(['message' => '']);
    }

    public function test_sensitive_resident_values_are_not_flashed_after_validation_failure(): void
    {
        $service = Service::factory()->create();

        $this->from(route('requests.create', $service))
            ->post(route('requests.store'), $this->residentPayload($service, [
                'contact_email' => 'not-an-email',
                'privacy_consent' => false,
            ]))
            ->assertSessionHasErrors(['contact_email', 'privacy_consent'])
            ->assertSessionMissing('_old_input.resident_name')
            ->assertSessionMissing('_old_input.contact_email')
            ->assertSessionMissing('_old_input.general_location')
            ->assertSessionMissing('_old_input.request_details')
            ->assertSessionHas('_old_input.service_slug', $service->slug);
    }

    public function test_staff_mutation_abuse_is_rate_limited_per_account(): void
    {
        $staff = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->create(['assigned_to' => $staff->id]);

        for ($attempt = 0; $attempt < 30; $attempt++) {
            $this->actingAs($staff)
                ->post(route('staff.requests.notes', $serviceRequest), ['body' => ''])
                ->assertSessionHasErrors('body');
        }

        $this->actingAs($staff)
            ->post(route('staff.requests.notes', $serviceRequest), ['body' => ''])
            ->assertTooManyRequests()
            ->assertInertia(fn (Assert $page) => $page
                ->component('errors/show')
                ->where('status', 429));

        $this->assertDatabaseCount('request_activities', 0);
    }

    public function test_administrator_mutation_abuse_is_rate_limited_per_account(): void
    {
        $administrator = User::factory()->create(['role' => UserRole::Administrator]);

        for ($attempt = 0; $attempt < 20; $attempt++) {
            $this->actingAs($administrator)
                ->patch(route('admin.settings.update'), [])
                ->assertSessionHasErrors('office_name_en');
        }

        $this->actingAs($administrator)
            ->patch(route('admin.settings.update'), [])
            ->assertTooManyRequests()
            ->assertInertia(fn (Assert $page) => $page
                ->component('errors/show')
                ->where('status', 429));
    }

    public function test_resident_submission_staff_processing_and_public_tracking_work_end_to_end(): void
    {
        Queue::fake();
        $service = Service::factory()->create(['appointment_required' => false]);
        $staff = User::factory()->create(['role' => UserRole::Staff]);

        $this->post(route('requests.store'), $this->residentPayload($service))->assertRedirect();
        $serviceRequest = ServiceRequest::query()->firstOrFail();
        $trackingPin = (string) session('resident_receipt.pin');

        $this->actingAs($staff)
            ->patch(route('staff.requests.assignment', $serviceRequest), ['assignee_id' => $staff->id])
            ->assertRedirect();
        $this->actingAs($staff)
            ->post(route('staff.requests.transitions', $serviceRequest), [
                'status' => ServiceRequestStatus::Acknowledged->value,
                'public_message_en' => '',
                'public_message_fil' => '',
                'private_note' => '',
            ])
            ->assertRedirect();

        $this->post(route('tracking.verify'), [
            'reference' => $serviceRequest->public_reference,
            'pin' => $trackingPin,
        ])->assertRedirect(route('tracking.show', ['reference' => $serviceRequest->public_reference]));

        $this->get(route('tracking.show', ['reference' => $serviceRequest->public_reference]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('tracking/show')
                ->where('trackedRequest.status', ServiceRequestStatus::Acknowledged->value)
                ->has('trackedRequest.history', 2)
                ->missing('trackedRequest.resident_name')
                ->missing('trackedRequest.private_note'));
    }

    /** @param array<string, mixed> $overrides */
    private function residentPayload(Service $service, array $overrides = []): array
    {
        return array_replace([
            'service_slug' => $service->slug,
            'resident_name' => 'Fictional Phase Six Resident',
            'contact_email' => 'phase-six@example.test',
            'contact_phone' => '',
            'preferred_contact' => 'email',
            'general_location' => 'Fictional Zone Six',
            'request_details' => 'This fictional request verifies the complete hardened workflow.',
            'appointment_requested' => false,
            'appointment_date' => '',
            'appointment_time_window' => '',
            'appointment_note' => '',
            'attachments' => [],
            'privacy_consent' => true,
            'website' => '',
        ], $overrides);
    }
}
