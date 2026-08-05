<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\UpdateRequestAssignmentRequest;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\RequestOperations;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class RequestAssignmentController extends Controller
{
    public function __invoke(
        UpdateRequestAssignmentRequest $request,
        ServiceRequest $serviceRequest,
        RequestOperations $operations,
    ): RedirectResponse {
        Gate::authorize('assign', $serviceRequest);
        $actor = $request->user();
        abort_unless($actor instanceof User, 403);
        $assigneeId = $request->validated('assignee_id');
        $assignee = $assigneeId !== null ? User::query()->findOrFail((int) $assigneeId) : null;

        $operations->assign($serviceRequest, $actor, $assignee);

        return back()->with('success', $assignee === null ? 'Request unassigned.' : "Request assigned to {$assignee->name}.");
    }
}
