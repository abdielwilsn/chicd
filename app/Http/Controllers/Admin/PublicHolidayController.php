<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePublicHolidayRequest;
use App\Models\PublicHoliday;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class PublicHolidayController extends Controller
{
    /**
     * Add a public holiday. No one is counted absent on it.
     */
    public function store(StorePublicHolidayRequest $request): RedirectResponse
    {
        PublicHoliday::create($request->safe()->only(['date', 'name']));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Public holiday added.')]);

        return back();
    }

    /**
     * Remove a public holiday added by mistake.
     */
    public function destroy(PublicHoliday $publicHoliday): RedirectResponse
    {
        $publicHoliday->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Public holiday removed.')]);

        return back();
    }
}
