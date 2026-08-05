<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicServiceResource;
use App\Models\Service;
use Inertia\Inertia;
use Inertia\Response;

class RequestFormController extends Controller
{
    public function __invoke(Service $service): Response
    {
        abort_unless($service->isPubliclyVisible(), 404);

        $service->load('requirements');

        return Inertia::render('requests/create', [
            'service' => PublicServiceResource::make($service),
            'appointmentDateBounds' => [
                'min' => now()->addDay()->toDateString(),
                'max' => now()->addDays(90)->toDateString(),
            ],
            'attachmentRules' => [
                'maxFiles' => (int) config('serbisyo.attachment_max_files', 5),
                'maxMegabytes' => (int) ceil(((int) config('serbisyo.attachment_max_kilobytes', 5120)) / 1024),
                'accept' => '.pdf,.jpg,.jpeg,.png',
            ],
        ]);
    }
}
