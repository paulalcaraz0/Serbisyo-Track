<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditEventType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AnnouncementFormRequest;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncementController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function index(): Response
    {
        Gate::authorize('viewAny', Announcement::class);

        return Inertia::render('admin/announcements/index', [
            'announcements' => AnnouncementResource::collection(
                Announcement::query()->orderByDesc('starts_at')->orderByDesc('id')->paginate(10)->withQueryString(),
            ),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Announcement::class);

        return Inertia::render('admin/announcements/create');
    }

    public function store(AnnouncementFormRequest $request): RedirectResponse
    {
        Gate::authorize('create', Announcement::class);
        $actor = $request->user();
        abort_unless($actor instanceof User, 403);

        DB::transaction(function () use ($request, $actor): void {
            $announcement = new Announcement;
            $announcement->fill($request->validated());
            $announcement->save();

            $this->auditLogger->record($actor, AuditEventType::AnnouncementCreated, 'announcement', $announcement->id, [
                'announcement_level' => $announcement->level,
            ]);
        });

        return to_route('admin.announcements.index')->with('success', 'Announcement published.');
    }

    public function edit(Announcement $announcement): Response
    {
        Gate::authorize('update', $announcement);

        return Inertia::render('admin/announcements/edit', [
            'announcement' => AnnouncementResource::make($announcement),
        ]);
    }

    public function update(AnnouncementFormRequest $request, Announcement $announcement): RedirectResponse
    {
        Gate::authorize('update', $announcement);
        $actor = $request->user();
        abort_unless($actor instanceof User, 403);

        DB::transaction(function () use ($announcement, $request, $actor): void {
            $announcement->fill($request->validated());
            $announcement->save();

            $this->auditLogger->record($actor, AuditEventType::AnnouncementUpdated, 'announcement', $announcement->id, [
                'announcement_level' => $announcement->level,
            ]);
        });

        return to_route('admin.announcements.index')->with('success', 'Announcement updated.');
    }

    public function destroy(Request $request, Announcement $announcement): RedirectResponse
    {
        Gate::authorize('delete', $announcement);
        $actor = $request->user();
        abort_unless($actor instanceof User, 403);

        $metadata = ['announcement_level' => $announcement->level];
        $identifier = $announcement->id;

        DB::transaction(function () use ($announcement, $actor, $metadata, $identifier): void {
            $announcement->delete();

            $this->auditLogger->record($actor, AuditEventType::AnnouncementDeleted, 'announcement', $identifier, $metadata);
        });

        return to_route('admin.announcements.index')->with('success', 'Announcement removed.');
    }
}
