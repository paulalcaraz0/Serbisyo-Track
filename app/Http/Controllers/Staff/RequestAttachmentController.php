<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RequestAttachmentController extends Controller
{
    public function __invoke(ServiceRequest $serviceRequest, string $attachment): StreamedResponse
    {
        Gate::authorize('downloadAttachment', $serviceRequest);
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
