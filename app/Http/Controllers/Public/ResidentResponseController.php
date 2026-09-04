<?php

namespace App\Http\Controllers\Public;

use App\Enums\RequestActivityType;
use App\Enums\ServiceRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Support\ResidentTrackingAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class ResidentResponseController extends Controller
{
    public function __invoke(Request $request, string $reference): RedirectResponse
    {
        $reference = strtoupper($reference);

        if (! ResidentTrackingAccess::allows($request, $reference)) {
            return redirect()->route('tracking.index')->withErrors([
                'reference' => __('phase3.tracking.access_expired'),
            ]);
        }

        $request->merge([
            'response_details' => trim((string) $request->input('response_details')),
        ]);

        $validated = $request->validate([
            'response_details' => ['required', 'string', 'min:10', 'max:2000'],
            'attachments' => ['nullable', 'array', 'max:'.config('serbisyo.attachment_max_files', 5)],
            'attachments.*' => ['file', File::types(['pdf', 'jpg', 'jpeg', 'png'])->max((int) config('serbisyo.attachment_max_kilobytes', 5120))],
            'website' => ['nullable', 'max:0'],
        ], [
            'response_details.required' => __('phase7.resident_response.validation.required'),
            'response_details.min' => __('phase7.resident_response.validation.min'),
            'response_details.max' => __('phase7.resident_response.validation.max'),
            'attachments.max' => __('phase3.validation.too_many_files'),
            'attachments.*.max' => __('phase3.validation.file_too_large'),
            'attachments.*.mimes' => __('phase3.validation.file_type'),
        ]);

        $storedFiles = [];

        /** @var array<int, UploadedFile> $uploadedFiles */
        $uploadedFiles = $request->file('attachments', []);

        try {
            DB::transaction(function () use ($validated, $reference, $uploadedFiles, &$storedFiles): void {
                $serviceRequest = ServiceRequest::query()
                    ->where('public_reference', $reference)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($serviceRequest->status !== ServiceRequestStatus::NeedsInformation) {
                    throw ValidationException::withMessages([
                        'response_details' => __('phase7.resident_response.validation.unavailable'),
                    ]);
                }

                $activity = $serviceRequest->activities()->create([
                    'event_type' => RequestActivityType::ResidentResponse,
                    'public_message_en' => Lang::get('phase7.resident_response.timeline_message', [], 'en'),
                    'public_message_fil' => Lang::get('phase7.resident_response.timeline_message', [], 'fil'),
                    'private_details' => $validated['response_details'],
                ]);

                foreach ($uploadedFiles as $file) {
                    $path = $file->store("requests/{$reference}/responses/{$activity->id}", 'local');

                    if ($path === false) {
                        throw new RuntimeException('The private response attachment could not be stored.');
                    }

                    $storedFiles[] = $path;
                    $serviceRequest->attachments()->create([
                        'request_activity_id' => $activity->id,
                        'public_id' => (string) Str::uuid(),
                        'disk' => 'local',
                        'path' => $path,
                        'original_name' => $this->safeOriginalName($file),
                        'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                        'size_bytes' => $file->getSize(),
                    ]);
                }

                $serviceRequest->touch();
            });
        } catch (Throwable $exception) {
            foreach ($storedFiles as $path) {
                Storage::disk('local')->delete($path);
            }

            throw $exception;
        }

        return redirect()
            ->route('tracking.show', ['reference' => $reference])
            ->with('success', __('phase7.resident_response.confirmation'));
    }

    private function safeOriginalName(UploadedFile $file): string
    {
        $name = basename(str_replace('\\', '/', $file->getClientOriginalName()));
        $name = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $name) ?? '';

        return Str::limit(trim($name), 180, '') ?: 'attachment';
    }
}
