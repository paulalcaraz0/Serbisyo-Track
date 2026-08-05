<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditEventType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOfficeSettingRequest;
use App\Http\Resources\OfficeSettingResource;
use App\Models\OfficeSetting;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class OfficeSettingController extends Controller
{
    public function edit(): Response
    {
        $settings = OfficeSetting::current();
        Gate::authorize('view', $settings);

        return Inertia::render('admin/settings/edit', [
            'officeSettings' => OfficeSettingResource::make($settings),
        ]);
    }

    public function update(UpdateOfficeSettingRequest $request, AuditLogger $auditLogger): RedirectResponse
    {
        $settings = OfficeSetting::current();
        Gate::authorize('update', $settings);
        $actor = $request->user();
        abort_unless($actor instanceof User, 403);

        DB::transaction(function () use ($settings, $request, $actor, $auditLogger): void {
            $settings->update($request->validated());
            $auditLogger->record($actor, AuditEventType::OfficeSettingsUpdated, 'office_settings', 1, [
                'retention_days' => $settings->retention_days,
            ]);
        });

        return to_route('admin.settings.edit')->with('success', 'Office and retention settings updated.');
    }
}
