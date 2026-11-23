<?php

use App\Models\CorrectionRequest;
use App\Models\FieldActivity;
use App\Models\Office;
use App\Models\OfficeAttendance;
use App\Models\Timesheet;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('staff cannot open the admin pages', function (string $routeName, array $parameters) {
    $staff = User::factory()->create();

    $this->actingAs($staff)->get(route($routeName, $parameters))->assertForbidden();
})->with([
    'attendance overview' => ['admin.attendance.index', []],
    'corrections' => ['admin.corrections.index', []],
    'consolidated download' => ['admin.timesheets.consolidated', ['month' => '2026-10', 'format' => 'csv']],
]);

test('admins see every staff member\'s month with totals, timesheet status and waiting corrections', function () {
    $this->travelTo('2026-11-20 10:00:00');
    $admin = User::factory()->admin()->create(['name' => 'Zara Admin']);
    $ada = User::factory()->create(['name' => 'Ada Okon']);
    $emem = User::factory()->create(['name' => 'Emem Udoh']);
    OfficeAttendance::factory()->for($ada)->clockedOut()->create([
        'clocked_in_at' => '2026-11-17 07:00:00',
        'clocked_out_at' => '2026-11-17 15:00:00',
        'clocked_in_within_geofence' => false,
    ]);
    FieldActivity::factory()->for($ada)->ended()->create([
        'started_at' => '2026-11-18 08:00:00',
        'ended_at' => '2026-11-18 09:30:00',
    ]);
    OfficeAttendance::factory()->for($ada)->clockedOut()->create(['clocked_in_at' => '2026-10-17 07:00:00']);
    CorrectionRequest::factory()->for($ada)->create(['work_date' => '2026-11-17']);
    Timesheet::factory()->for($emem)->approved()->create(['month' => '2026-11-01', 'approved_by' => $admin->id]);

    $response = $this->actingAs($admin)->get(route('admin.attendance.index'));

    $response->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('admin/attendance/Index')
        ->where('month', '2026-11')
        ->where('staff.0.name', 'Ada Okon')
        ->where('staff.0.totals.officeMinutes', 480)
        ->where('staff.0.totals.fieldMinutes', 90)
        ->where('staff.0.totals.daysWorked', 2)
        ->where('staff.0.totals.flaggedClockings', 1)
        ->where('staff.0.pendingCorrections', 1)
        ->where('staff.0.status', 'pending')
        ->where('staff.1.name', 'Emem Udoh')
        ->where('staff.1.status', 'approved')
        ->where('staff.1.totals.daysWorked', 0)
        ->where('staff.2.name', 'Zara Admin')
        ->where('pendingCorrectionsTotal', 1)
    );
});

test('admins see who is working right now, oldest first', function () {
    $this->travelTo('2026-11-20 10:00:00');
    $office = Office::factory()->create(['name' => 'Uyo Office']);
    $inOffice = OfficeAttendance::factory()->for($office)->create(['clocked_in_at' => '2026-11-20 07:00:00']);
    $inField = FieldActivity::factory()->create([
        'activity' => 'School visit',
        'location' => 'Eket',
        'started_at' => '2026-11-20 06:30:00',
    ]);
    OfficeAttendance::factory()->clockedOut()->create(['clocked_in_at' => '2026-11-20 06:00:00']);

    $response = $this->actingAs(User::factory()->admin()->create())->get(route('admin.attendance.index'));

    $response->assertInertia(fn (Assert $page) => $page
        ->has('workingNow', 2)
        ->where('workingNow.0.key', 'field-'.$inField->id)
        ->where('workingNow.0.where', 'School visit · Eket')
        ->where('workingNow.0.since', 'Fri 20 Nov, 07:30')
        ->where('workingNow.1.key', 'office-'.$inOffice->id)
        ->where('workingNow.1.where', 'Uyo Office')
    );
});
