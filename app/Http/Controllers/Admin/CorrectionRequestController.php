<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CorrectionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CloseCorrectionRequestRequest;
use App\Http\Resources\CorrectionRequestResource;
use App\Models\CorrectionRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CorrectionRequestController extends Controller
{
    /**
     * The correction requests waiting for a decision, oldest first, and the
     * most recent ones dealt with.
     */
    public function index(): Response
    {
        $relations = ['correctable', 'user', 'resolver'];

        return Inertia::render('admin/corrections/Index', [
            'pending' => CorrectionRequestResource::collection(
                CorrectionRequest::query()->pending()->with($relations)->oldest()->oldest('id')->get()
            )->resolve(),
            'recentlyClosed' => CorrectionRequestResource::collection(
                CorrectionRequest::query()->whereNot('status', CorrectionStatus::Pending)->with($relations)->latest('resolved_at')->latest('id')->limit(30)->get()
            )->resolve(),
        ]);
    }

    /**
     * Close a request without editing a record: declined, or resolved some
     * other way (for example, a day with no record to correct).
     */
    public function update(CloseCorrectionRequestRequest $request, CorrectionRequest $correctionRequest): RedirectResponse
    {
        abort_unless($correctionRequest->isPending(), 409, 'This request has already been dealt with.');

        $correctionRequest->close(
            CorrectionStatus::from($request->string('status')->toString()),
            $request->user(),
            $request->string('note')->toString(),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Correction request closed.')]);

        return back();
    }
}
