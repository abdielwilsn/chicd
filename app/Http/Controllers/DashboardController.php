<?php

namespace App\Http\Controllers;

use App\Http\Resources\FieldActivityResource;
use App\Http\Resources\OfficeAttendanceResource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Show the staff member's Today page: Office and Field/Activity modes,
     * anything still running, and what they have recorded today.
     */
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $today = now(config('chicd.timezone'))->startOfDay();
        $tomorrow = $today->addDay();

        $openOfficeAttendance = $user->officeAttendances()->open()->with('office')->latest('clocked_in_at')->first();
        $openFieldActivity = $user->fieldActivities()->open()->latest('started_at')->first();

        return Inertia::render('Dashboard', [
            'today' => $today->format('l j F Y'),
            'openOfficeAttendance' => $openOfficeAttendance ? OfficeAttendanceResource::make($openOfficeAttendance)->resolve() : null,
            'openFieldActivity' => $openFieldActivity ? FieldActivityResource::make($openFieldActivity)->resolve() : null,
            'officeAttendances' => OfficeAttendanceResource::collection(
                $user->officeAttendances()->startedBetween($today, $tomorrow)->with('office')->oldest('clocked_in_at')->oldest('id')->get()
            )->resolve(),
            'fieldActivities' => FieldActivityResource::collection(
                $user->fieldActivities()->startedBetween($today, $tomorrow)->oldest('started_at')->oldest('id')->get()
            )->resolve(),
            'programmes' => config('chicd.field_programmes'),
        ]);
    }
}
