<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\RequestActivityType;
use App\Enums\ServiceRequestStatus;
use App\Models\RequestAttachment;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ResidentRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_service_has_a_guided_request_form_with_public_data_only(): void
    {
        $service = Service::factory()->create();

        $this->get(route('requests.create', $service))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')
            ->assertInertia(fn (Assert $page) => $page
                ->component('requests/create')
                ->where('service.data.slug', $service->slug)
                ->where('appointmentDateBounds.min', now()->addDay()->toDateString())
                ->where('attachmentRules.maxFiles', 5)
                ->missing('service.data.id')
                ->missing('service.data.is_active'));
    }

    public function test_inactive_service_cannot_display_or_accept_a_request(): void
    {
        $service = Service::factory()->inactive()->create();

        $this->get(route('requests.create', $service))->assertNotFound();
        $this->from(route('services.index'))
            ->post(route('requests.store'), $this->payload($service))
            ->assertRedirect(route('services.index'))
            ->assertSessionHasErrors('service_slug');

        $this->assertDatabaseCount('service_requests', 0);
    }

    public function test_resident_can_submit_an_encrypted_request_with_appointment_and_private_attachment(): void
    {
        Storage::fake('local');
        $service = Service::factory()->create(['appointment_required' => true]);
        $payload = $this->payload($service, [
            'attachments' => [UploadedFile::fake()->create('fictional-proof.jpg', 100, 'image/jpeg')],
        ]);

        $response = $this->post(route('requests.store'), $payload);
        $serviceRequest = ServiceRequest::query()->firstOrFail();

        $response->assertRedirect(route('requests.receipt', $serviceRequest));
        $this->assertSame(ServiceRequestStatus::Submitted, $serviceRequest->status);
        $this->assertMatchesRegularExpression('/^ST-[2-9A-HJ-NP-Z]{4}-[2-9A-HJ-NP-Z]{4}-[2-9A-HJ-NP-Z]{4}$/', $serviceRequest->public_reference);
        $this->assertTrue(Hash::check((string) session('resident_receipt.pin'), $serviceRequest->tracking_pin_hash));
        $this->assertSame('Fictional Resident', $serviceRequest->resident_name);

        $rawRecord = DB::table('service_requests')->first();
        $this->assertNotSame('Fictional Resident', $rawRecord->resident_name);
        $this->assertNotSame('resident@example.test', $rawRecord->contact_email);
        $this->assertStringNotContainsString((string) session('resident_receipt.pin'), $rawRecord->tracking_pin_hash);

        $appointment = $serviceRequest->appointment()->firstOrFail();
        $this->assertSame(now()->addWeek()->toDateString(), $appointment->preferred_date?->toDateString());
        $this->assertSame('morning', $appointment->preferred_time_window);
        $this->assertSame(AppointmentStatus::Requested, $appointment->status);
        $attachment = $serviceRequest->attachments()->firstOrFail();
        Storage::disk('local')->assertExists($attachment->path);
        $this->assertSame('fictional-proof.jpg', $attachment->original_name);
    }

    public function test_receipt_shows_the_pin_once_and_remains_available_without_personal_data(): void
    {
        $service = Service::factory()->create();
        $this->post(route('requests.store'), $this->payload($service, ['appointment_requested' => false]))->assertRedirect();
        $serviceRequest = ServiceRequest::query()->firstOrFail();

        $this->get(route('requests.receipt', $serviceRequest))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('requests/receipt')
                ->where('receipt.reference', $serviceRequest->public_reference)
                ->where('receipt.serviceName', $service->name_en)
                ->where('receipt.pin', fn ($pin) => is_string($pin) && strlen($pin) === 6)
                ->missing('receipt.resident_name')
                ->missing('receipt.contact_email')
                ->missing('receipt.request_details'));

        $this->get(route('requests.receipt', $serviceRequest))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('receipt.pin', null));
    }

    public function test_receipt_is_forbidden_without_a_submission_or_tracking_grant(): void
    {
        $serviceRequest = ServiceRequest::factory()->create();

        $this->get(route('requests.receipt', $serviceRequest))->assertForbidden();
    }

    public function test_required_appointment_and_contact_fields_are_enforced(): void
    {
        $service = Service::factory()->create(['appointment_required' => true]);

        $this->from(route('requests.create', $service))
            ->post(route('requests.store'), $this->payload($service, [
                'contact_email' => '',
                'appointment_date' => '',
                'appointment_time_window' => '',
            ]))
            ->assertRedirect(route('requests.create', $service))
            ->assertSessionHasErrors(['contact_email', 'appointment_date', 'appointment_time_window']);

        $this->assertDatabaseCount('service_requests', 0);
    }

    public function test_attachment_type_count_and_size_are_restricted(): void
    {
        Storage::fake('local');
        $service = Service::factory()->create();

        $this->from(route('requests.create', $service))
            ->post(route('requests.store'), $this->payload($service, [
                'attachments' => [UploadedFile::fake()->create('script.exe', 10, 'application/x-msdownload')],
            ]))
            ->assertSessionHasErrors('attachments.0');

        $this->from(route('requests.create', $service))
            ->post(route('requests.store'), $this->payload($service, [
                'attachments' => [UploadedFile::fake()->create('large.pdf', 5121, 'application/pdf')],
            ]))
            ->assertSessionHasErrors('attachments.0');

        $this->from(route('requests.create', $service))
            ->post(route('requests.store'), $this->payload($service, [
                'attachments' => array_map(
                    fn (int $index) => UploadedFile::fake()->create("file-{$index}.pdf", 10, 'application/pdf'),
                    range(1, 6),
                ),
            ]))
            ->assertSessionHasErrors('attachments');

        $this->assertDatabaseCount('service_requests', 0);
    }

    public function test_valid_reference_and_pin_create_a_short_lived_redacted_tracking_view(): void
    {
        $serviceRequest = ServiceRequest::factory()->create([
            'resident_name' => 'Private Fictional Name',
            'request_details' => 'Private fictional request details for testing only.',
        ]);

        $this->post(route('tracking.verify'), [
            'reference' => strtolower($serviceRequest->public_reference),
            'pin' => '123456',
        ])->assertRedirect(route('tracking.show', ['reference' => $serviceRequest->public_reference]));

        $this->get(route('tracking.show', ['reference' => $serviceRequest->public_reference]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('tracking/show')
                ->where('trackedRequest.reference', $serviceRequest->public_reference)
                ->where('trackedRequest.status', 'submitted')
                ->where('trackedRequest.statusLabel', 'Submitted')
                ->missing('trackedRequest.resident_name')
                ->missing('trackedRequest.contact_email')
                ->missing('trackedRequest.request_details'));
    }

    public function test_invalid_reference_and_pin_return_the_same_generic_error(): void
    {
        $serviceRequest = ServiceRequest::factory()->create();

        $wrongPin = $this->from(route('tracking.index'))->post(route('tracking.verify'), [
            'reference' => $serviceRequest->public_reference,
            'pin' => '999999',
        ]);
        $unknownReference = $this->from(route('tracking.index'))->post(route('tracking.verify'), [
            'reference' => 'ST-AAAA-BBBB-CCCC',
            'pin' => '999999',
        ]);

        $wrongPin->assertSessionHasErrors(['reference' => __('phase3.tracking.invalid')]);
        $unknownReference->assertSessionHasErrors(['reference' => __('phase3.tracking.invalid')]);
    }

    public function test_expired_tracking_grant_returns_to_the_secure_lookup(): void
    {
        $serviceRequest = ServiceRequest::factory()->create();

        $this->withSession([
            'resident_tracking_grants' => [$serviceRequest->public_reference => now()->subMinute()->timestamp],
        ])->get(route('tracking.show', ['reference' => $serviceRequest->public_reference]))
            ->assertRedirect(route('tracking.index'))
            ->assertSessionHasErrors('reference');
    }

    public function test_tracking_attempts_are_rate_limited_by_reference(): void
    {
        $serviceRequest = ServiceRequest::factory()->create();

        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->post(route('tracking.verify'), [
                'reference' => $serviceRequest->public_reference,
                'pin' => '999999',
            ])->assertRedirect();
        }

        $this->post(route('tracking.verify'), [
            'reference' => $serviceRequest->public_reference,
            'pin' => '999999',
        ])->assertTooManyRequests();
    }

    public function test_private_attachment_download_requires_a_matching_tracking_grant(): void
    {
        Storage::fake('local');
        $serviceRequest = ServiceRequest::factory()->create();
        $attachment = RequestAttachment::factory()->for($serviceRequest)->create();
        Storage::disk('local')->put($attachment->path, 'fictional document');
        $url = route('tracking.attachments.show', [
            'reference' => $serviceRequest->public_reference,
            'attachment' => $attachment->public_id,
        ]);

        $this->get($url)->assertForbidden();

        $this->withSession([
            'resident_tracking_grants' => [$serviceRequest->public_reference => now()->addMinutes(5)->timestamp],
        ])->get($url)
            ->assertOk()
            ->assertDownload('fictional-document.pdf')
            ->assertHeader('Cache-Control', 'no-store, private');

        $otherAttachment = RequestAttachment::factory()->create();
        $this->withSession([
            'resident_tracking_grants' => [$serviceRequest->public_reference => now()->addMinutes(5)->timestamp],
        ])->get(route('tracking.attachments.show', [
            'reference' => $serviceRequest->public_reference,
            'attachment' => $otherAttachment->public_id,
        ]))->assertNotFound();
    }

    public function test_resident_can_securely_provide_requested_information_and_staff_can_review_it(): void
    {
        Storage::fake('local');
        $staff = User::factory()->create();
        $serviceRequest = ServiceRequest::factory()->create([
            'assigned_to' => $staff->id,
            'status' => ServiceRequestStatus::NeedsInformation,
        ]);
        $serviceRequest->activities()->create([
            'actor_id' => $staff->id,
            'event_type' => RequestActivityType::StatusChange,
            'from_status' => ServiceRequestStatus::Acknowledged,
            'to_status' => ServiceRequestStatus::NeedsInformation,
            'public_message_en' => 'Please provide a fictional supporting document.',
            'public_message_fil' => 'Magbigay ng kathang-isip na supporting document.',
        ]);
        $residentMessage = 'Here is the fictional supporting information requested by staff.';

        $this->withSession([
            'resident_tracking_grants' => [$serviceRequest->public_reference => now()->addMinutes(5)->timestamp],
        ])->post(route('tracking.responses.store', ['reference' => $serviceRequest->public_reference]), [
            'response_details' => $residentMessage,
            'attachments' => [UploadedFile::fake()->create('additional-proof.pdf', 100, 'application/pdf')],
            'website' => '',
        ])->assertRedirect(route('tracking.show', ['reference' => $serviceRequest->public_reference]))
            ->assertSessionHas('success', __('phase7.resident_response.confirmation'));

        $responseActivity = $serviceRequest->activities()->reorder()->latest('id')->firstOrFail();
        $this->assertSame(RequestActivityType::ResidentResponse, $responseActivity->event_type);
        $this->assertSame($residentMessage, $responseActivity->private_details);
        $this->assertNull($responseActivity->actor_id);
        $this->assertSame(ServiceRequestStatus::NeedsInformation, $serviceRequest->fresh()->status);

        $rawActivity = DB::table('request_activities')->where('id', $responseActivity->id)->first();
        $this->assertNotSame($residentMessage, $rawActivity->private_details);

        $attachment = $responseActivity->attachments()->firstOrFail();
        $this->assertSame($serviceRequest->id, $attachment->service_request_id);
        $this->assertSame('additional-proof.pdf', $attachment->original_name);
        Storage::disk('local')->assertExists($attachment->path);

        $this->get(route('tracking.show', ['reference' => $serviceRequest->public_reference]))
            ->assertOk()
            ->assertDontSee($residentMessage)
            ->assertInertia(fn (Assert $page) => $page
                ->where('trackedRequest.canRespond', true)
                ->where('trackedRequest.requestedInformationMessage', 'Please provide a fictional supporting document.')
                ->where('trackedRequest.history.1.message', __('phase7.resident_response.timeline_message'))
                ->where('trackedRequest.attachments.0.name', 'additional-proof.pdf')
                ->where('attachmentRules.maxFiles', 5)
                ->missing('trackedRequest.private_details'));

        $this->actingAs($staff)
            ->get(route('staff.requests.show', $serviceRequest))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('requestRecord.data.activities.1.event_type', RequestActivityType::ResidentResponse->value)
                ->where('requestRecord.data.activities.1.private_details', $residentMessage)
                ->where('requestRecord.data.activities.1.attachments.0.name', 'additional-proof.pdf'));
    }

    public function test_resident_response_requires_tracking_access_and_needs_information_status(): void
    {
        $serviceRequest = ServiceRequest::factory()->create(['status' => ServiceRequestStatus::NeedsInformation]);
        $payload = [
            'response_details' => 'Fictional additional information for staff review.',
            'attachments' => [],
            'website' => '',
        ];

        $this->post(route('tracking.responses.store', ['reference' => $serviceRequest->public_reference]), $payload)
            ->assertRedirect(route('tracking.index'))
            ->assertSessionHasErrors('reference');

        $this->assertDatabaseCount('request_activities', 0);

        $serviceRequest->update(['status' => ServiceRequestStatus::InProgress]);

        $this->withSession([
            'resident_tracking_grants' => [$serviceRequest->public_reference => now()->addMinutes(5)->timestamp],
        ])->post(route('tracking.responses.store', ['reference' => $serviceRequest->public_reference]), $payload)
            ->assertRedirect()
            ->assertSessionHasErrors('response_details');

        $this->assertDatabaseCount('request_activities', 0);
    }

    public function test_resident_response_validation_rejects_unsafe_files_and_does_not_flash_private_text(): void
    {
        Storage::fake('local');
        $serviceRequest = ServiceRequest::factory()->create(['status' => ServiceRequestStatus::NeedsInformation]);

        $this->withSession([
            'resident_tracking_grants' => [$serviceRequest->public_reference => now()->addMinutes(5)->timestamp],
        ])->post(route('tracking.responses.store', ['reference' => $serviceRequest->public_reference]), [
            'response_details' => 'short',
            'attachments' => [UploadedFile::fake()->create('unsafe.exe', 10, 'application/x-msdownload')],
            'website' => '',
        ])->assertSessionHasErrors(['response_details', 'attachments.0'])
            ->assertSessionMissing('_old_input.response_details')
            ->assertSessionMissing('_old_input.attachments');

        $this->assertDatabaseCount('request_activities', 0);
        $this->assertDatabaseCount('request_attachments', 0);
    }

    public function test_resident_responses_are_rate_limited_by_reference_and_ip(): void
    {
        $serviceRequest = ServiceRequest::factory()->create(['status' => ServiceRequestStatus::NeedsInformation]);
        $this->withSession([
            'resident_tracking_grants' => [$serviceRequest->public_reference => now()->addMinutes(5)->timestamp],
        ]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('tracking.responses.store', ['reference' => $serviceRequest->public_reference]), [
                'response_details' => 'short',
                'attachments' => [],
                'website' => '',
            ])->assertSessionHasErrors('response_details');
        }

        $this->post(route('tracking.responses.store', ['reference' => $serviceRequest->public_reference]), [
            'response_details' => 'short',
            'attachments' => [],
            'website' => '',
        ])->assertTooManyRequests();

        $this->assertDatabaseCount('request_activities', 0);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(Service $service, array $overrides = []): array
    {
        return array_replace([
            'service_slug' => $service->slug,
            'resident_name' => 'Fictional Resident',
            'contact_email' => 'resident@example.test',
            'contact_phone' => '',
            'preferred_contact' => 'email',
            'general_location' => 'Fictional Zone 2',
            'request_details' => 'This is a fictional resident request used only for automated testing.',
            'appointment_requested' => true,
            'appointment_date' => now()->addWeek()->toDateString(),
            'appointment_time_window' => 'morning',
            'appointment_note' => 'A fictional scheduling preference.',
            'attachments' => [],
            'privacy_consent' => true,
            'website' => '',
        ], $overrides);
    }
}
