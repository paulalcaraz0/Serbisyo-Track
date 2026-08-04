<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicServiceResource;
use App\Models\Service;
use Inertia\Inertia;
use Inertia\Response;

class ServiceDetailsController extends Controller
{
    public function __invoke(Service $service): Response
    {
        abort_unless($service->isPubliclyVisible(), 404);

        $service->load('requirements');

        return Inertia::render('services/show', [
            'service' => PublicServiceResource::make($service),
        ]);
    }
}
