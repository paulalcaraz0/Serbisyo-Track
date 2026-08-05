<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Support\ResidentTrackingAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RequestAttachmentController extends Controller
{
    public function __invoke(Request $request, string $reference, string $attachment): StreamedResponse
    {
        $reference = strtoupper($reference);
        abort_unless(ResidentTrackingAccess::allows($request, $reference), 403);

        $serviceRequest = ServiceRequest::query()->where('public_reference', $reference)->firstOrFail();
        $requestAttachment = $serviceRequest->attachments()->where('public_id', $attachment)->firstOrFail();
        abort_unless(Storage::disk($requestAttachment->disk)->exists($requestAttachment->path), 404);

        return Storage::disk($requestAttachment->disk)->download(
            $requestAttachment->path,
            $requestAttachment->original_name,
            [
                'Content-Type' => $requestAttachment->mime_type,
                'Cache-Control' => 'no-store, private',
                'X-Content-Type-Options' => 'nosniff',
            ],
        );
    }
}
