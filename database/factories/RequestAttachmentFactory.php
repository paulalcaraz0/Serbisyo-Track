<?php

namespace Database\Factories;

use App\Models\RequestAttachment;
use App\Models\ServiceRequest;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<RequestAttachment>
 */
class RequestAttachmentFactory extends Factory
{
    public function definition(): array
    {
        $publicId = (string) Str::uuid();

        return [
            'service_request_id' => ServiceRequest::factory(),
            'public_id' => $publicId,
            'disk' => 'local',
            'path' => "requests/testing/{$publicId}.pdf",
            'original_name' => 'fictional-document.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 1024,
        ];
    }
}
