<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClockInRequest;
use App\Http\Requests\ClockOutRequest;
use App\Models\Office;
use App\Models\OfficeAttendance;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class OfficeAttendanceController extends Controller
{
    /**
     * Clock in at the office.
     *
     * The time comes from the server, not the device. A location outside the
     * geofence is recorded and flagged for review rather than refused.
     */
    public function store(ClockInRequest $request): RedirectResponse
    {
        $location = $request->locationAttributes('clock_in');
        $geofence = Office::checkGeofence($location['clock_in_latitude'], $location['clock_in_longitude']);

        $request->user()->officeAttendances()->create([
            ...$location,
            'office_id' => $geofence['office_id'],
            'clocked_in_at' => now(),
            'clock_in_distance_metres' => $geofence['distance_metres'],
            'clocked_in_within_geofence' => $geofence['within_geofence'],
        ]);

        Inertia::flash('toast', $geofence['within_geofence']
            ? ['type' => 'success', 'message' => __('Clocked in.')]
            : ['type' => 'warning', 'message' => __('Clocked in. Your location was outside the office area, so this clock-in has been flagged for review.')]);

        return to_route('dashboard');
    }

    /**
     * Clock out of an open office session, checked against the same office
     * the session was clocked in at.
     */
    public function update(ClockOutRequest $request, OfficeAttendance $officeAttendance): RedirectResponse
    {
        $location = $request->locationAttributes('clock_out');
        $geofence = Office::checkGeofence(
            $location['clock_out_latitude'],
            $location['clock_out_longitude'],
            $officeAttendance->office,
        );

        $officeAttendance->update([
            ...$location,
            'clocked_out_at' => now(),
            'clock_out_distance_metres' => $geofence['distance_metres'],
            'clocked_out_within_geofence' => $geofence['within_geofence'],
        ]);

        Inertia::flash('toast', $geofence['within_geofence']
            ? ['type' => 'success', 'message' => __('Clocked out.')]
            : ['type' => 'warning', 'message' => __('Clocked out. Your location was outside the office area, so this clock-out has been flagged for review.')]);

        return to_route('dashboard');
    }
}
