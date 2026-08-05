<?php

namespace App\Http\Controllers\Staff;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\UpdateRequestAppointmentRequest;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\RequestOperations;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class RequestAppointmentController extends Controller
{
    public function __invoke(
        UpdateRequestAppointmentRequest $request,
        ServiceRequest $serviceRequest,
        RequestOperations $operations,
    ): RedirectResponse {
        Gate::authorize('manageAppointment', $serviceRequest);
        $actor = $request->user();
        abort_unless($actor instanceof User, 403);
        $validated = $request->validated();
        $confirmedStart = isset($validated['confirmed_start_at'])
            ? CarbonImmutable::parse($validated['confirmed_start_at'])
            : null;

        $operations->updateAppointment(
            $serviceRequest,
            $actor,
            AppointmentStatus::from($validated['status']),
            $confirmedStart,
            $validated['private_note'] ?? null,
        );

        return back()->with('success', 'Appointment updated and the public update was queued for delivery when email is preferred.');
    }
}
