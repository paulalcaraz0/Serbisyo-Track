<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Support\ResidentTrackingAccess;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RequestReceiptController extends Controller
{
    public function __invoke(Request $request, ServiceRequest $serviceRequest): Response
    {
        $pendingReceipt = $request->session()->pull('resident_receipt');
        $isInitialView = is_array($pendingReceipt)
            && isset($pendingReceipt['reference'], $pendingReceipt['pin'])
            && is_string($pendingReceipt['reference'])
            && hash_equals($serviceRequest->public_reference, $pendingReceipt['reference']);

        abort_unless($isInitialView || ResidentTrackingAccess::allows($request, $serviceRequest->public_reference), 403);

        $serviceRequest->load(['service', 'appointment', 'attachments']);
        $filipino = app()->getLocale() === 'fil';

        return Inertia::render('requests/receipt', [
            'receipt' => [
                'reference' => $serviceRequest->public_reference,
                'pin' => $isInitialView ? $pendingReceipt['pin'] : null,
                'serviceName' => $filipino ? $serviceRequest->service->name_fil : $serviceRequest->service->name_en,
                'submittedAt' => $serviceRequest->submitted_at->toIso8601String(),
                'appointment' => $serviceRequest->appointment ? [
                    'date' => $serviceRequest->appointment->preferred_date->toDateString(),
                    'timeWindow' => $serviceRequest->appointment->preferred_time_window,
                ] : null,
                'attachments' => $serviceRequest->attachments->map(fn ($attachment) => [
                    'name' => $attachment->original_name,
                    'sizeBytes' => $attachment->size_bytes,
                ])->values(),
            ],
        ]);
    }
}
