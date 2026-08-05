<?php

namespace App\Http\Controllers\Public;

use App\Enums\AppointmentStatus;
use App\Enums\RequestActivityType;
use App\Enums\ServiceRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Public\StoreServiceRequestRequest;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Support\BusinessDayCalculator;
use App\Support\PublicRequestMessages;
use App\Support\ResidentTrackingAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ServiceRequestController extends Controller
{
    public function store(StoreServiceRequestRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $service = Service::query()->publiclyVisible()->where('slug', $validated['service_slug'])->firstOrFail();
        $reference = ServiceRequest::generateReference();
        $trackingPin = (string) random_int(100000, 999999);
        $storedFiles = [];

        /** @var array<int, UploadedFile> $uploadedFiles */
        $uploadedFiles = $request->file('attachments', []);

        try {
            $serviceRequest = DB::transaction(function () use ($request, $validated, $service, $reference, $trackingPin, $uploadedFiles, &$storedFiles) {
                $submittedAt = now();
                $serviceRequest = ServiceRequest::query()->create([
                    'service_id' => $service->id,
                    'public_reference' => $reference,
                    'tracking_pin_hash' => Hash::make($trackingPin),
                    'status' => ServiceRequestStatus::Submitted,
                    'locale' => app()->getLocale(),
                    'resident_name' => $validated['resident_name'],
                    'contact_email' => $validated['contact_email'],
                    'contact_phone' => $validated['contact_phone'],
                    'preferred_contact' => $validated['preferred_contact'],
                    'general_location' => $validated['general_location'],
                    'request_details' => $validated['request_details'],
                    'consented_at' => $submittedAt,
                    'submitted_at' => $submittedAt,
                    'due_at' => BusinessDayCalculator::add($submittedAt, $service->target_business_days),
                ]);

                $submittedMessages = PublicRequestMessages::status(ServiceRequestStatus::Submitted);
                $serviceRequest->activities()->create([
                    'event_type' => RequestActivityType::Submitted,
                    'to_status' => ServiceRequestStatus::Submitted,
                    'public_message_en' => $submittedMessages['en'],
                    'public_message_fil' => $submittedMessages['fil'],
                ]);

                if ($service->appointment_required || $request->boolean('appointment_requested')) {
                    $serviceRequest->appointment()->create([
                        'preferred_date' => $validated['appointment_date'],
                        'preferred_time_window' => $validated['appointment_time_window'],
                        'resident_note' => $validated['appointment_note'],
                        'status' => AppointmentStatus::Requested,
                    ]);
                }

                foreach ($uploadedFiles as $file) {
                    $path = $file->store("requests/{$reference}", 'local');

                    if ($path === false) {
                        throw new RuntimeException('The private attachment could not be stored.');
                    }

                    $storedFiles[] = $path;
                    $serviceRequest->attachments()->create([
                        'public_id' => (string) Str::uuid(),
                        'disk' => 'local',
                        'path' => $path,
                        'original_name' => $this->safeOriginalName($file),
                        'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                        'size_bytes' => $file->getSize(),
                    ]);
                }

                return $serviceRequest;
            });
        } catch (Throwable $exception) {
            foreach ($storedFiles as $path) {
                Storage::disk('local')->delete($path);
            }

            throw $exception;
        }

        ResidentTrackingAccess::grant($request, $reference);
        $request->session()->flash('resident_receipt', [
            'reference' => $reference,
            'pin' => $trackingPin,
        ]);

        return redirect()->route('requests.receipt', $serviceRequest);
    }

    private function safeOriginalName(UploadedFile $file): string
    {
        $name = basename(str_replace('\\', '/', $file->getClientOriginalName()));
        $name = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $name) ?? '';

        return Str::limit(trim($name), 180, '') ?: 'attachment';
    }
}
