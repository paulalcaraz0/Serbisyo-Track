<?php

namespace Database\Seeders;

use App\Enums\AppointmentStatus;
use App\Enums\RequestActivityType;
use App\Enums\ServiceRequestStatus;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Support\PublicRequestMessages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RequestSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        $administrator = User::query()->where('email', 'admin@serbisyotrack.test')->firstOrFail();
        $staff = User::query()->where('email', 'staff@serbisyotrack.test')->firstOrFail();

        foreach ($this->requests() as $data) {
            $service = Service::query()->where('slug', $data['service_slug'])->firstOrFail();
            $status = $data['status'];
            $assigned = $data['assigned'];
            $appointment = $data['appointment'];

            $serviceRequest = ServiceRequest::query()->updateOrCreate(
                ['public_reference' => $data['public_reference']],
                [
                    'service_id' => $service->id,
                    'tracking_pin_hash' => Hash::make('246824'),
                    'status' => $status,
                    'locale' => 'en',
                    'resident_name' => $data['resident_name'],
                    'contact_email' => $data['contact_email'],
                    'contact_phone' => null,
                    'preferred_contact' => 'email',
                    'general_location' => $data['general_location'],
                    'request_details' => $data['request_details'],
                    'consented_at' => now()->subDays($data['age_days']),
                    'submitted_at' => now()->subDays($data['age_days']),
                    'assigned_to' => $assigned ? $staff->id : null,
                    'assigned_at' => $assigned ? now()->subDays(max(0, $data['age_days'] - 1)) : null,
                    'due_at' => now()->addDays($data['due_offset_days'])->endOfDay(),
                    'closed_at' => null,
                ],
            );

            $serviceRequest->activities()->delete();
            $serviceRequest->appointment()->delete();

            $submitted = PublicRequestMessages::status(ServiceRequestStatus::Submitted);
            $serviceRequest->activities()->create([
                'event_type' => RequestActivityType::Submitted,
                'to_status' => ServiceRequestStatus::Submitted,
                'public_message_en' => $submitted['en'],
                'public_message_fil' => $submitted['fil'],
                'created_at' => $serviceRequest->submitted_at,
            ]);

            if ($assigned) {
                $serviceRequest->activities()->create([
                    'actor_id' => $administrator->id,
                    'subject_user_id' => $staff->id,
                    'event_type' => RequestActivityType::Assignment,
                    'private_details' => 'Fictional demonstration assignment.',
                    'created_at' => $serviceRequest->assigned_at,
                ]);
            }

            $previousStatus = ServiceRequestStatus::Submitted;

            foreach ($this->statusPath($status) as $position => $timelineStatus) {
                $message = PublicRequestMessages::status($timelineStatus);
                $serviceRequest->activities()->create([
                    'actor_id' => $staff->id,
                    'event_type' => RequestActivityType::StatusChange,
                    'from_status' => $previousStatus,
                    'to_status' => $timelineStatus,
                    'public_message_en' => $message['en'],
                    'public_message_fil' => $message['fil'],
                    'private_details' => 'Fictional demonstration processing event.',
                    'created_at' => now()->subDays(max(0, $data['age_days'] - $position - 2)),
                ]);

                $previousStatus = $timelineStatus;
            }

            if ($appointment) {
                $serviceRequest->appointment()->create([
                    'preferred_date' => now()->addDays(10)->toDateString(),
                    'preferred_time_window' => 'morning',
                    'resident_note' => 'Fictional morning preference.',
                    'status' => AppointmentStatus::Requested,
                ]);
            }
        }
    }

    /** @return array<int, ServiceRequestStatus> */
    private function statusPath(ServiceRequestStatus $status): array
    {
        return match ($status) {
            ServiceRequestStatus::Submitted => [],
            ServiceRequestStatus::Acknowledged => [ServiceRequestStatus::Acknowledged],
            ServiceRequestStatus::InProgress => [ServiceRequestStatus::Acknowledged, ServiceRequestStatus::InProgress],
            default => [ServiceRequestStatus::Acknowledged, $status],
        };
    }

    /** @return array<int, array<string, mixed>> */
    private function requests(): array
    {
        return [
            [
                'public_reference' => 'ST-DEMA-RQST-AAAA',
                'service_slug' => 'barangay-clearance',
                'status' => ServiceRequestStatus::Submitted,
                'assigned' => false,
                'resident_name' => 'Maya Demo',
                'contact_email' => 'maya.demo@example.test',
                'general_location' => 'Fictional Zone 1',
                'request_details' => 'A fictional clearance request for portfolio demonstration and staff queue testing.',
                'age_days' => 1,
                'due_offset_days' => 1,
                'appointment' => false,
            ],
            [
                'public_reference' => 'ST-DEMB-RQST-BBBB',
                'service_slug' => 'certificate-of-residency',
                'status' => ServiceRequestStatus::Acknowledged,
                'assigned' => true,
                'resident_name' => 'Noel Demo',
                'contact_email' => 'noel.demo@example.test',
                'general_location' => 'Fictional Zone 2',
                'request_details' => 'A fictional residency certificate request for the protected staff workflow demonstration.',
                'age_days' => 2,
                'due_offset_days' => 0,
                'appointment' => false,
            ],
            [
                'public_reference' => 'ST-DEMC-RQST-CCCC',
                'service_slug' => 'barangay-business-clearance',
                'status' => ServiceRequestStatus::InProgress,
                'assigned' => true,
                'resident_name' => 'Lina Demo',
                'contact_email' => 'lina.demo@example.test',
                'general_location' => 'Fictional Market Area',
                'request_details' => 'A fictional business clearance request intentionally marked overdue for dashboard demonstration.',
                'age_days' => 7,
                'due_offset_days' => -2,
                'appointment' => false,
            ],
            [
                'public_reference' => 'ST-DEMD-RQST-DDDD',
                'service_slug' => 'community-facility-reservation',
                'status' => ServiceRequestStatus::Acknowledged,
                'assigned' => true,
                'resident_name' => 'Aris Demo',
                'contact_email' => 'aris.demo@example.test',
                'general_location' => 'Fictional Riverside',
                'request_details' => 'A fictional community facility reservation request with an appointment preference.',
                'age_days' => 3,
                'due_offset_days' => 2,
                'appointment' => true,
            ],
        ];
    }
}
