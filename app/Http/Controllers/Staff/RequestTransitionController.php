<?php

namespace App\Http\Controllers\Staff;

use App\Enums\ServiceRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\TransitionServiceRequestRequest;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\RequestWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class RequestTransitionController extends Controller
{
    public function __invoke(
        TransitionServiceRequestRequest $request,
        ServiceRequest $serviceRequest,
        RequestWorkflow $workflow,
    ): RedirectResponse {
        Gate::authorize('transition', $serviceRequest);
        $actor = $request->user();
        abort_unless($actor instanceof User, 403);
        $validated = $request->validated();

        $workflow->transition(
            $serviceRequest,
            $actor,
            ServiceRequestStatus::from($validated['status']),
            $validated['public_message_en'] ?? null,
            $validated['public_message_fil'] ?? null,
            $validated['private_note'] ?? null,
        );

        return back()->with('success', 'Request status updated and the public update was queued for delivery when email is preferred.');
    }
}
