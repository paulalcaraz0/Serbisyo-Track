<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\TrackServiceRequestRequest;
use App\Models\ServiceRequest;
use App\Support\ResidentTrackingAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class RequestTrackingController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('tracking/index');
    }

    public function verify(TrackServiceRequestRequest $request): RedirectResponse
    {
        $reference = $request->string('reference')->toString();
        $serviceRequest = ServiceRequest::query()->where('public_reference', $reference)->first();
        $hash = $serviceRequest->tracking_pin_hash ?? '$2y$12$5c6HphFq7D3iJjW/JXWXYePTfaw8HAHI13/GwhYOZXzwHO2frT6yW';

        if (! Hash::check($request->string('pin')->toString(), $hash) || $serviceRequest === null) {
            throw ValidationException::withMessages([
                'reference' => __('phase3.tracking.invalid'),
            ]);
        }

        ResidentTrackingAccess::grant($request, $reference);

        return redirect()->route('tracking.show', ['reference' => $reference]);
    }

    public function show(Request $request, string $reference): Response|RedirectResponse
    {
        $reference = strtoupper($reference);

        if (! ResidentTrackingAccess::allows($request, $reference)) {
            return redirect()->route('tracking.index')->withErrors([
                'reference' => __('phase3.tracking.access_expired'),
            ]);
        }

        $serviceRequest = ServiceRequest::query()
            ->where('public_reference', $reference)
            ->with(['service', 'appointment', 'attachments', 'activities'])
            ->firstOrFail();
        $filipino = app()->getLocale() === 'fil';
        $status = $serviceRequest->status->value;

        return Inertia::render('tracking/show', [
            'trackedRequest' => [
                'reference' => $serviceRequest->public_reference,
                'serviceName' => $filipino ? $serviceRequest->service->name_fil : $serviceRequest->service->name_en,
                'status' => $status,
                'statusLabel' => __("phase3.statuses.{$status}.label"),
                'statusDescription' => __("phase3.statuses.{$status}.description"),
                'submittedAt' => $serviceRequest->submitted_at->toIso8601String(),
                'updatedAt' => $serviceRequest->updated_at->toIso8601String(),
                'appointment' => $serviceRequest->appointment ? [
                    'preferredDate' => $serviceRequest->appointment->preferred_date->toDateString(),
                    'preferredTimeWindow' => $serviceRequest->appointment->preferred_time_window,
                    'status' => $serviceRequest->appointment->status->value,
                ] : null,
                'attachments' => $serviceRequest->attachments->map(fn ($attachment) => [
                    'publicId' => $attachment->public_id,
                    'name' => $attachment->original_name,
                    'sizeBytes' => $attachment->size_bytes,
                ])->values(),
                'history' => $serviceRequest->activities
                    ->filter(fn ($activity) => $activity->public_message_en !== null && $activity->public_message_fil !== null)
                    ->map(fn ($activity) => [
                        'status' => $activity->to_status?->value,
                        'message' => $filipino ? $activity->public_message_fil : $activity->public_message_en,
                        'occurredAt' => $activity->created_at->toIso8601String(),
                    ])->values(),
            ],
        ]);
    }
}
