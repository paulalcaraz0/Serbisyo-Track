<?php

namespace App\Http\Controllers\Staff;

use App\Enums\ServiceRequestStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\StaffRequestResource;
use App\Http\Resources\StaffRequestSummaryResource;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\RequestWorkflow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RequestController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', ServiceRequest::class);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['all', ...array_column(ServiceRequestStatus::cases(), 'value')])],
            'assignment' => ['nullable', Rule::in(['all', 'mine', 'unassigned', 'assigned', 'mine_and_unassigned'])],
            'service' => ['nullable', 'string', 'max:160'],
            'overdue' => ['nullable', 'boolean'],
            'sort' => ['nullable', Rule::in(['newest', 'oldest', 'updated', 'due'])],
        ]);
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $defaultAssignment = $user->role === UserRole::Administrator ? 'all' : 'mine_and_unassigned';
        $filters['assignment'] ??= $defaultAssignment;

        $query = ServiceRequest::query()->with(['service', 'assignee', 'appointment']);
        $this->applyFilters($query, $filters, $user);

        return Inertia::render('staff/requests/index', [
            'requests' => StaffRequestSummaryResource::collection($query->paginate(15)->withQueryString()),
            'filters' => [
                'search' => $filters['search'] ?? '',
                'status' => $filters['status'] ?? 'all',
                'assignment' => $filters['assignment'],
                'service' => $filters['service'] ?? 'all',
                'overdue' => (bool) ($filters['overdue'] ?? false),
                'sort' => $filters['sort'] ?? 'oldest',
            ],
            'summary' => [
                'open' => ServiceRequest::query()->whereNull('closed_at')->count(),
                'mine' => ServiceRequest::query()->whereNull('closed_at')->where('assigned_to', $user->id)->count(),
                'unassigned' => ServiceRequest::query()->whereNull('closed_at')->whereNull('assigned_to')->count(),
                'overdue' => ServiceRequest::query()->whereNull('closed_at')->whereNotNull('due_at')->where('due_at', '<', now())->count(),
            ],
            'services' => Service::query()->whereHas('requests')->orderBy('name_en')->get(['slug', 'name_en']),
        ]);
    }

    public function show(ServiceRequest $serviceRequest, RequestWorkflow $workflow, Request $request): Response
    {
        Gate::authorize('view', $serviceRequest);
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $serviceRequest->load(['service', 'assignee', 'appointment', 'attachments', 'activities.actor', 'activities.subjectUser', 'activities.attachments']);
        $mayTransition = $user->can('transition', $serviceRequest);
        $staffQuery = User::query()->where('is_active', true)->whereNotNull('email_verified_at')->orderBy('name');

        if ($user->role !== UserRole::Administrator) {
            $staffQuery->whereKey($user->id);
        }

        return Inertia::render('staff/requests/show', [
            'requestRecord' => StaffRequestResource::make($serviceRequest),
            'staffOptions' => $staffQuery->get()->map(fn (User $staff) => [
                'id' => $staff->id,
                'name' => $staff->name,
                'role' => $staff->role->value,
            ])->values(),
            'allowedTransitions' => $mayTransition
                ? array_map(fn (ServiceRequestStatus $status) => [
                    'value' => $status->value,
                    'label' => __("phase3.statuses.{$status->value}.label"),
                ], $workflow->allowedTransitions($serviceRequest->status))
                : [],
        ]);
    }

    /**
     * @param  Builder<ServiceRequest>  $query
     * @param  array<string, mixed>  $filters
     */
    private function applyFilters(Builder $query, array $filters, User $user): void
    {
        if (isset($filters['search']) && is_string($filters['search']) && $filters['search'] !== '') {
            $search = $filters['search'];
            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('public_reference', 'like', "%{$search}%")
                    ->orWhereHas('service', fn (Builder $serviceQuery) => $serviceQuery
                        ->where('name_en', 'like', "%{$search}%")
                        ->orWhere('name_fil', 'like', "%{$search}%"));
            });
        }

        if (($filters['status'] ?? 'all') !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['service']) && $filters['service'] !== 'all') {
            $query->whereHas('service', fn (Builder $serviceQuery) => $serviceQuery->where('slug', $filters['service']));
        }

        match ($filters['assignment'] ?? 'all') {
            'mine' => $query->where('assigned_to', $user->id),
            'unassigned' => $query->whereNull('assigned_to'),
            'assigned' => $query->whereNotNull('assigned_to'),
            'mine_and_unassigned' => $query->where(fn (Builder $builder) => $builder->where('assigned_to', $user->id)->orWhereNull('assigned_to')),
            default => null,
        };

        if ((bool) ($filters['overdue'] ?? false)) {
            $query->whereNull('closed_at')->whereNotNull('due_at')->where('due_at', '<', now());
        }

        match ($filters['sort'] ?? 'oldest') {
            'newest' => $query->orderByDesc('submitted_at'),
            'updated' => $query->orderByDesc('updated_at'),
            'due' => $query->orderBy('due_at')->orderBy('submitted_at'),
            default => $query->orderBy('submitted_at'),
        };
    }
}
