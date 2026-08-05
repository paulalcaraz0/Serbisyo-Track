<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditEventType;
use App\Http\Controllers\Controller;
use App\Http\Resources\AuditEventResource;
use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AuditEventController extends Controller
{
    public function __invoke(Request $request): Response
    {
        Gate::authorize('viewAny', AuditEvent::class);
        $filters = $request->validate([
            'action' => ['nullable', Rule::in(['all', ...array_column(AuditEventType::cases(), 'value')])],
            'actor' => ['nullable', 'integer', 'exists:users,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $query = AuditEvent::query()->with('actor:id,name');

        if (isset($filters['action']) && $filters['action'] !== 'all') {
            $query->where('action', $filters['action']);
        }

        if (isset($filters['actor'])) {
            $query->where('actor_id', $filters['actor']);
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from'].' 00:00:00');
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to'].' 23:59:59');
        }

        return Inertia::render('admin/audit/index', [
            'events' => AuditEventResource::collection($query->latest('id')->paginate(20)->withQueryString()),
            'filters' => [
                'action' => $filters['action'] ?? 'all',
                'actor' => isset($filters['actor']) ? (string) $filters['actor'] : '',
                'date_from' => $filters['date_from'] ?? '',
                'date_to' => $filters['date_to'] ?? '',
            ],
            'actions' => collect(AuditEventType::cases())->map(fn (AuditEventType $action) => [
                'value' => $action->value,
                'label' => $action->label(),
            ])->values(),
            'actors' => User::query()->whereHas('auditEvents')->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
