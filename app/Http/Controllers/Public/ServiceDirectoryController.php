<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicServiceResource;
use App\Models\Service;
use Inertia\Inertia;
use Inertia\Response;

class ServiceDirectoryController extends Controller
{
    public function __invoke(): Response
    {
        $nameColumn = app()->getLocale() === 'fil' ? 'name_fil' : 'name_en';
        $services = Service::query()
            ->publiclyVisible()
            ->orderBy($nameColumn)
            ->get();

        return Inertia::render('services/index', [
            'services' => PublicServiceResource::collection($services),
        ]);
    }
}
