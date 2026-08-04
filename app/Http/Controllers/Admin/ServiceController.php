<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreServiceRequest;
use App\Http\Requests\Admin\UpdateServiceRequest;
use App\Http\Resources\AdminServiceResource;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Service::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:all,active,inactive,archived'],
            'sort' => ['nullable', 'in:name,updated'],
            'direction' => ['nullable', 'in:asc,desc'],
        ]);

        $query = Service::query()->with('requirements');
        $this->applyFilters($query, $filters);

        $services = $query
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/services/index', [
            'services' => AdminServiceResource::collection($services),
            'filters' => [
                'search' => $filters['search'] ?? '',
                'status' => $filters['status'] ?? 'all',
                'sort' => $filters['sort'] ?? 'updated',
                'direction' => $filters['direction'] ?? 'desc',
            ],
            'summary' => [
                'active' => Service::query()->whereNull('archived_at')->where('is_active', true)->count(),
                'inactive' => Service::query()->whereNull('archived_at')->where('is_active', false)->count(),
                'archived' => Service::query()->whereNotNull('archived_at')->count(),
            ],
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Service::class);

        return Inertia::render('admin/services/create');
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        Gate::authorize('create', Service::class);

        $data = $request->validated();
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $service = DB::transaction(function () use ($data, $user): Service {
            $service = new Service;
            $service->fill(Arr::except($data, ['requirements']));
            $service->slug = $this->uniqueSlug($data['name_en']);
            $service->created_by = $user->id;
            $service->updated_by = $user->id;
            $service->save();

            $this->replaceRequirements($service, $data['requirements']);

            return $service;
        });

        return to_route('admin.services.edit', $service)->with('success', 'Service created successfully.');
    }

    public function edit(Service $service): Response
    {
        Gate::authorize('update', $service);

        return Inertia::render('admin/services/edit', [
            'service' => AdminServiceResource::make($service->load('requirements')),
        ]);
    }

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        Gate::authorize('update', $service);

        $data = $request->validated();
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        DB::transaction(function () use ($data, $service, $user): void {
            $service->fill(Arr::except($data, ['requirements']));
            $service->updated_by = $user->id;
            $service->save();

            $this->replaceRequirements($service, $data['requirements']);
        });

        return to_route('admin.services.edit', $service)->with('success', 'Service updated successfully.');
    }

    public function archive(Request $request, Service $service): RedirectResponse
    {
        Gate::authorize('update', $service);

        $service->forceFill([
            'is_active' => false,
            'archived_at' => now(),
            'updated_by' => $request->user()?->getAuthIdentifier(),
        ])->save();

        return to_route('admin.services.index')->with('success', 'Service archived. Existing history is preserved.');
    }

    public function restore(Request $request, Service $service): RedirectResponse
    {
        Gate::authorize('restore', $service);

        $service->forceFill([
            'is_active' => false,
            'archived_at' => null,
            'updated_by' => $request->user()?->getAuthIdentifier(),
        ])->save();

        return to_route('admin.services.edit', $service)->with('success', 'Service restored as inactive. Review it before activation.');
    }

    /**
     * @param  Builder<Service>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(Builder $query, array $filters): void
    {
        if (isset($filters['search']) && is_string($filters['search']) && $filters['search'] !== '') {
            $search = $filters['search'];
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('name_en', 'like', "%{$search}%")
                    ->orWhere('name_fil', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        match ($filters['status'] ?? 'all') {
            'active' => $query->whereNull('archived_at')->where('is_active', true),
            'inactive' => $query->whereNull('archived_at')->where('is_active', false),
            'archived' => $query->whereNotNull('archived_at'),
            default => null,
        };

        $sortColumn = ($filters['sort'] ?? 'updated') === 'name' ? 'name_en' : 'updated_at';
        $direction = ($filters['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortColumn, $direction);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'service';
        $slug = $base;
        $suffix = 2;

        while (Service::query()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * @param  array<int, array<string, mixed>>  $requirements
     */
    private function replaceRequirements(Service $service, array $requirements): void
    {
        $service->requirements()->delete();

        foreach ($requirements as $index => $requirement) {
            $service->requirements()->create([
                ...$requirement,
                'sort_order' => $index,
            ]);
        }
    }
}
