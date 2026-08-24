<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditEventType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HolidayFormRequest;
use App\Http\Resources\HolidayResource;
use App\Models\Holiday;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class HolidayController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function index(): Response
    {
        Gate::authorize('viewAny', Holiday::class);

        return Inertia::render('admin/holidays/index', [
            'holidays' => HolidayResource::collection(
                Holiday::query()->orderByDesc('date')->paginate(10)->withQueryString(),
            ),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Holiday::class);

        return Inertia::render('admin/holidays/create');
    }

    public function store(HolidayFormRequest $request): RedirectResponse
    {
        Gate::authorize('create', Holiday::class);
        $actor = $request->user();
        abort_unless($actor instanceof User, 403);

        DB::transaction(function () use ($request, $actor): void {
            $holiday = new Holiday;
            $holiday->fill($request->validated());
            $holiday->save();

            $this->auditLogger->record($actor, AuditEventType::HolidayCreated, 'holiday', $holiday->date->toDateString(), [
                'holiday_date' => $holiday->date->toDateString(),
                'is_recurring' => $holiday->is_recurring,
            ]);
        });

        return to_route('admin.holidays.index')->with('success', 'Holiday saved.');
    }

    public function edit(Holiday $holiday): Response
    {
        Gate::authorize('update', $holiday);

        return Inertia::render('admin/holidays/edit', [
            'holiday' => HolidayResource::make($holiday),
        ]);
    }

    public function update(HolidayFormRequest $request, Holiday $holiday): RedirectResponse
    {
        Gate::authorize('update', $holiday);
        $actor = $request->user();
        abort_unless($actor instanceof User, 403);

        DB::transaction(function () use ($holiday, $request, $actor): void {
            $holiday->fill($request->validated());
            $holiday->save();

            $this->auditLogger->record($actor, AuditEventType::HolidayUpdated, 'holiday', $holiday->date->toDateString(), [
                'holiday_date' => $holiday->date->toDateString(),
                'is_recurring' => $holiday->is_recurring,
            ]);
        });

        return to_route('admin.holidays.index')->with('success', 'Holiday updated.');
    }

    public function destroy(Request $request, Holiday $holiday): RedirectResponse
    {
        Gate::authorize('delete', $holiday);
        $actor = $request->user();
        abort_unless($actor instanceof User, 403);

        $metadata = [
            'holiday_date' => $holiday->date->toDateString(),
            'is_recurring' => $holiday->is_recurring,
        ];
        $identifier = $holiday->date->toDateString();

        DB::transaction(function () use ($holiday, $actor, $metadata, $identifier): void {
            $holiday->delete();

            $this->auditLogger->record($actor, AuditEventType::HolidayDeleted, 'holiday', $identifier, $metadata);
        });

        return to_route('admin.holidays.index')->with('success', 'Holiday removed.');
    }
}
