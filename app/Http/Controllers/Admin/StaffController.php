<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStaffRequest;
use App\Http\Requests\Admin\UpdateStaffRequest;
use App\Http\Resources\StaffAccountResource;
use App\Models\User;
use App\Services\StaffAccountManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class StaffController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', User::class);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'in:all,staff,administrator'],
            'status' => ['nullable', 'in:all,active,inactive'],
        ]);

        $query = User::query()->withCount([
            'assignedRequests as open_assignments_count' => fn (Builder $builder) => $builder->whereNull('closed_at'),
        ]);

        if (isset($filters['search']) && $filters['search'] !== '') {
            $search = (string) $filters['search'];
            $query->where(fn (Builder $builder) => $builder
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }

        if (isset($filters['role']) && $filters['role'] !== 'all') {
            $query->where('role', $filters['role']);
        }

        match ($filters['status'] ?? 'all') {
            'active' => $query->where('is_active', true),
            'inactive' => $query->where('is_active', false),
            default => null,
        };

        return Inertia::render('admin/staff/index', [
            'staffAccounts' => StaffAccountResource::collection($query->orderBy('name')->paginate(12)->withQueryString()),
            'filters' => [
                'search' => $filters['search'] ?? '',
                'role' => $filters['role'] ?? 'all',
                'status' => $filters['status'] ?? 'all',
            ],
            'summary' => [
                'active' => User::query()->where('is_active', true)->count(),
                'inactive' => User::query()->where('is_active', false)->count(),
                'administrators' => User::query()->where('role', UserRole::Administrator)->where('is_active', true)->count(),
            ],
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', User::class);

        return Inertia::render('admin/staff/create');
    }

    public function store(StoreStaffRequest $request, StaffAccountManager $manager): RedirectResponse
    {
        Gate::authorize('create', User::class);
        $actor = $request->user();
        abort_unless($actor instanceof User, 403);
        $staff = $manager->create($actor, $request->validated());

        return to_route('admin.staff.edit', $staff)->with('success', 'Staff account created successfully.');
    }

    public function edit(User $user): Response
    {
        Gate::authorize('update', $user);
        $user->loadCount(['assignedRequests as open_assignments_count' => fn (Builder $builder) => $builder->whereNull('closed_at')]);

        return Inertia::render('admin/staff/edit', [
            'staffAccount' => StaffAccountResource::make($user),
        ]);
    }

    public function update(UpdateStaffRequest $request, User $user, StaffAccountManager $manager): RedirectResponse
    {
        Gate::authorize('update', $user);
        $actor = $request->user();
        abort_unless($actor instanceof User, 403);
        $manager->update($actor, $user, $request->validated());

        return to_route('admin.staff.edit', $user)->with('success', 'Staff account updated successfully.');
    }
}
