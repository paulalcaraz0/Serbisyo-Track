<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StoreInternalNoteRequest;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Services\RequestOperations;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class InternalNoteController extends Controller
{
    public function __invoke(StoreInternalNoteRequest $request, ServiceRequest $serviceRequest, RequestOperations $operations): RedirectResponse
    {
        Gate::authorize('addInternalNote', $serviceRequest);
        $actor = $request->user();
        abort_unless($actor instanceof User, 403);

        $operations->addInternalNote($serviceRequest, $actor, $request->string('body')->toString());

        return back()->with('success', 'Internal note added. It is never shown in public tracking.');
    }
}
