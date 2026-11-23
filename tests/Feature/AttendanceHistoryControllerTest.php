<?php

use App\Models\CorrectionRequest;
use App\Models\FieldActivity;
use App\Models\OfficeAttendance;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('attendance.index'));

    $response->assertRedirect(route('login'));
});

test('staff see their own records for the month grouped by day, newest day first', function () {
    $this->travelTo('2026-11-20 10:00:00');
    $user = User::factory()->create();
    $officeDay = OfficeAttendance::factory()->for($user)->clockedOut()->create([
        'clocked_in_at' => '2026-11-17 07:00:00',
        'clocked_out_at' => '2026-11-17 15:30:00',
    ]);
    $firstActivity = FieldActivity::factory()->for($user)->ended()->create([
        'started_at' => '2026-11-18 08:00:00',
        'ended_at' => '2026-11-18 10:15:00',
    ]);
    $secondActivity = FieldActivity::factory()->for($user)->ended()->create([
        'started_at' => '2026-11-18 11:00:00',
        'ended_at' => '2026-11-18 12:00:00',
    ]);
    OfficeAttendance::factory()->clockedOut()->create(['clocked_in_at' => '2026-11-18 07:00:00']);

    $response = $this->actingAs($user)->get(route('attendance.index'));

    $response->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('attendance/Index')
        ->where('month', '2026-11')
        ->where('monthLabel', 'November 2026')
        ->where('previousMonth', '2026-10')
        ->where('nextMonth', null)
        ->has('days', 2)
        ->where('days.0.date', '2026-11-18')
        ->where('days.0.label', 'Wednesday 18 November')
        ->has('days.0.officeAttendances', 0)
        ->where('days.0.fieldActivities.0.id', $firstActivity->id)
        ->where('days.0.fieldActivities.0.started_time', '09:00')
        ->where('days.0.fieldActivities.1.id', $secondActivity->id)
        ->where('days.1.date', '2026-11-17')
        ->where('days.1.officeAttendances.0.id', $officeDay->id)
        ->where('days.1.officeAttendances.0.minutes', 510)
        ->where('totals', [
            'officeMinutes' => 510,
            'fieldMinutes' => 195,
            'officeDays' => 1,
            'fieldDays' => 1,
        ])
    );
});

test('days and months follow Nigerian time, not UTC', function () {
    $this->travelTo('2026-12-05 10:00:00');
    $user = User::factory()->create();
    $justAfterMidnightLagos = OfficeAttendance::factory()->for($user)->create(['clocked_in_at' => '2026-11-30 23:30:00']);

    $response = $this->actingAs($user)->get(route('attendance.index', ['month' => '2026-12']));

    $response->assertInertia(fn (Assert $page) => $page
        ->has('days', 1)
        ->where('days.0.date', '2026-12-01')
        ->where('days.0.officeAttendances.0.id', $justAfterMidnightLagos->id)
        ->where('days.0.officeAttendances.0.clocked_in_time', '00:30')
    );
});

test('an earlier month can be viewed and links forward to the next month', function () {
    $this->travelTo('2026-11-20 10:00:00');
    $user = User::factory()->create();
    $october = FieldActivity::factory()->for($user)->ended()->create(['started_at' => '2026-10-14 08:00:00']);
    FieldActivity::factory()->for($user)->ended()->create(['started_at' => '2026-11-03 08:00:00']);

    $response = $this->actingAs($user)->get(route('attendance.index', ['month' => '2026-10']));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('month', '2026-10')
        ->where('nextMonth', '2026-11')
        ->has('days', 1)
        ->where('days.0.fieldActivities.0.id', $october->id)
    );
});

test('a malformed or future month falls back to the current month', function (string $month) {
    $this->travelTo('2026-11-20 10:00:00');

    $response = $this->actingAs(User::factory()->create())->get(route('attendance.index', ['month' => $month]));

    $response->assertOk()->assertInertia(fn (Assert $page) => $page->where('month', '2026-11'));
})->with([
    'not a date' => 'november',
    'month 13' => '2026-13',
    'future month' => '2027-01',
]);

test('open sessions are listed but not counted towards the totals', function () {
    $this->travelTo('2026-11-18 10:00:00');
    $user = User::factory()->create();
    OfficeAttendance::factory()->for($user)->create(['clocked_in_at' => '2026-11-18 07:00:00']);

    $response = $this->actingAs($user)->get(route('attendance.index'));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('days.0.officeAttendances.0.clocked_out_time', null)
        ->where('days.0.officeAttendances.0.minutes', null)
        ->where('totals.officeMinutes', 0)
        ->where('totals.officeDays', 1)
    );
});

test('a correction request about the last day of the month is listed with that month', function () {
    $this->travelTo('2026-12-05 10:00:00');
    $user = User::factory()->create();
    $request = CorrectionRequest::factory()->for($user)->create(['work_date' => '2026-11-30']);

    $this->actingAs($user)->get(route('attendance.index', ['month' => '2026-11']))
        ->assertInertia(fn (Assert $page) => $page->where('correctionRequests.0.id', $request->id));
});

test('staff see their lateness, absence and leave for the month', function () {
    config(['chicd.working_hours' => ['days' => [1, 2, 3, 4, 5], 'start' => '08:00', 'grace_minutes' => 15]]);
    $this->travelTo('2026-11-04 10:00:00');
    $user = User::factory()->create(['created_at' => '2026-10-01 09:00:00']);
    OfficeAttendance::factory()->for($user)->clockedOut()->create(['clocked_in_at' => '2026-11-02 08:30:00', 'clocked_out_at' => '2026-11-02 15:00:00']);

    $this->actingAs($user)->get(route('attendance.index', ['month' => '2026-11']))->assertInertia(fn (Assert $page) => $page
        ->where('attendance.lateDays.0.date', '2026-11-02')
        ->where('attendance.absentDays.0.date', '2026-11-03')
        ->where('lateAfter', "08:00 (with 15 minutes' grace)")
    );
});
