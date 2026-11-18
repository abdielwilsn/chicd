<?php

namespace App\Http\Controllers;

use App\Http\Requests\EndFieldActivityRequest;
use App\Http\Requests\StartFieldActivityRequest;
use App\Models\FieldActivity;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class FieldActivityController extends Controller
{
    /**
     * Start a field activity. The time comes from the server, not the device.
     */
    public function store(StartFieldActivityRequest $request): RedirectResponse
    {
        $request->user()->fieldActivities()->create([
            ...$request->safe()->only(['programme', 'activity', 'description', 'location']),
            ...$request->locationAttributes('start'),
            'started_at' => now(),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Activity started.')]);

        return to_route('dashboard');
    }

    /**
     * End a running field activity.
     */
    public function update(EndFieldActivityRequest $request, FieldActivity $fieldActivity): RedirectResponse
    {
        $fieldActivity->update([
            ...$request->locationAttributes('end'),
            'ended_at' => now(),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Activity ended.')]);

        return to_route('dashboard');
    }
}
