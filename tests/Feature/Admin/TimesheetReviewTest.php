<?php

use App\Enums\TimesheetStatus;
use App\Models\CorrectionRequest;
use App\Models\FieldActivity;
use App\Models\OfficeAttendance;
use App\Models\RecordChange;
use App\Models\Timesheet;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->travelTo('2026-12-05 10:00:00');
    $this->admin = User::factory()->admin()->create(['name' => 'Zara Admin']);
    $this->staff = User::factory()->create(['name' => 'Ada Okon']);
});

test('staff cannot review timesheets', function () {
    $this->actingAs($this->staff)
        ->get(route('admin.timesheets.show', ['user' => $this->staff, 'month' => '2026-11']))
        ->assertForbidden();
});

test('admins see the month\'s records with the locations captured', function () {
    $attendance = OfficeAttendance::factory()->for($this->staff)->clockedOut()->create([
        'clocked_in_at' => '2026-11-17 07:00:00',
        'clocked_out_at' => '2026-11-17 15:00:00',
        'clock_in_latitude' => 5.0377,
        'clock_in_longitude' => 7.9128,
        'clock_in_distance_metres' => 640,
        'clocked_in_within_geofence' => false,
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.timesheets.show', ['user' => $this->staff, 'month' => '2026-11']));

    $response->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('admin/timesheets/Show')
        ->where('staff.name', 'Ada Okon')
        ->where('timesheet.status', 'pending')
        ->where('timesheet.approvalBlockers', [])
        ->where('days.0.officeAttendances.0.id', $attendance->id)
        ->where('days.0.officeAttendances.0.flagged', true)
        ->where('days.0.officeAttendances.0.clock_in_location.latitude', 5.0377)
        ->where('days.0.officeAttendances.0.clock_in_location.distance_metres', 640)
        ->where('days.0.officeAttendances.0.edit.clocked_in_at', '2026-11-17T08:00')
    );
});

test('admins approve a finished month and the approval goes in the audit trail', function () {
    OfficeAttendance::factory()->for($this->staff)->clockedOut()->create(['clocked_in_at' => '2026-11-17 07:00:00', 'clocked_out_at' => '2026-11-17 15:00:00']);

    $this->actingAs($this->admin)
        ->post(route('admin.timesheets.approve', ['user' => $this->staff, 'month' => '2026-11']))
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $timesheet = Timesheet::sole();
    expect($timesheet->status)->toBe(TimesheetStatus::Approved)
        ->and($timesheet->month->toDateString())->toBe('2026-11-01')
        ->and($timesheet->approved_by)->toBe($this->admin->id)
        ->and($timesheet->recordChanges()->sole()->action)->toBe('approved');
});

test('a month cannot be approved while records are open, corrections are waiting, or it has not ended', function () {
    OfficeAttendance::factory()->for($this->staff)->create(['clocked_in_at' => '2026-11-17 07:00:00']);
    CorrectionRequest::factory()->for($this->staff)->create(['work_date' => '2026-11-18']);

    $this->actingAs($this->admin)
        ->post(route('admin.timesheets.approve', ['user' => $this->staff, 'month' => '2026-11']))
        ->assertSessionHasErrors('approval');

    $this->actingAs($this->admin)
        ->post(route('admin.timesheets.approve', ['user' => $this->staff, 'month' => '2026-12']))
        ->assertSessionHasErrors(['approval' => 'The month has not ended yet.']);

    expect(Timesheet::count())->toBe(0);
});

test('reopening needs a reason, which goes in the audit trail', function () {
    $timesheet = Timesheet::factory()->for($this->staff)->approved()->create(['month' => '2026-11-01']);
    $route = route('admin.timesheets.reopen', ['user' => $this->staff, 'month' => '2026-11']);

    $this->actingAs($this->admin)->delete($route, ['reason' => ''])->assertSessionHasErrors('reason');

    $this->actingAs($this->admin)->delete($route, ['reason' => 'Ada reported a missing day.'])->assertSessionHasNoErrors();

    expect($timesheet->fresh()->status)->toBe(TimesheetStatus::Pending)
        ->and($timesheet->fresh()->approved_by)->toBeNull()
        ->and(RecordChange::sole()->reason)->toBe('Ada reported a missing day.');
});

test('admins download an individual timesheet even before it is approved', function () {
    FieldActivity::factory()->for($this->staff)->ended()->create(['started_at' => '2026-11-18 08:00:00', 'ended_at' => '2026-11-18 09:00:00']);

    $response = $this->actingAs($this->admin)->get(route('admin.timesheets.download', ['user' => $this->staff, 'month' => '2026-11', 'format' => 'csv']));

    $response->assertOk()->assertDownload('chicd-timesheet-ada-okon-2026-11.csv');
    expect($response->streamedContent())->toContain('"Ada Okon",'.$this->staff->email.',"Not yet approved",2026-11-18,Field');
});

test('admins download a consolidated timesheet for everyone', function (string $format) {
    OfficeAttendance::factory()->for($this->staff)->clockedOut()->create(['clocked_in_at' => '2026-11-17 07:00:00', 'clocked_out_at' => '2026-11-17 15:00:00']);
    FieldActivity::factory()->for($this->admin)->ended()->create(['started_at' => '2026-11-18 08:00:00', 'ended_at' => '2026-11-18 09:00:00']);

    $response = $this->actingAs($this->admin)->get(route('admin.timesheets.consolidated', ['month' => '2026-11', 'format' => $format]));

    $response->assertOk()->assertDownload('chicd-consolidated-timesheet-2026-11.'.$format);

    if ($format === 'csv') {
        expect($response->streamedContent())
            ->toContain('"Ada Okon",')
            ->toContain('"Zara Admin",');
    }
})->with(['csv', 'pdf']);

test('the audit trail lists changes to the month\'s records and the timesheet, newest first', function () {
    $attendance = OfficeAttendance::factory()->for($this->staff)->clockedOut()->create(['clocked_in_at' => '2026-11-17 07:00:00', 'clocked_out_at' => '2026-11-17 15:00:00']);
    $attendance->logChange($this->admin, 'corrected', 'Late clock-in.', ['clocked_in_at' => ['from' => '2026-11-17 08:00', 'to' => '2026-11-17 08:30']]);
    $this->travel(1)->minute();
    $timesheet = Timesheet::factory()->for($this->staff)->create(['month' => '2026-11-01']);
    $timesheet->logChange($this->admin, 'reopened', 'Missing day reported.');
    OfficeAttendance::factory()->clockedOut()->create(['clocked_in_at' => '2026-11-17 07:00:00'])
        ->logChange($this->admin, 'corrected', 'Someone else.');

    $response = $this->actingAs($this->admin)->get(route('admin.timesheets.show', ['user' => $this->staff, 'month' => '2026-11']));

    $response->assertInertia(fn (Assert $page) => $page
        ->has('history', 2)
        ->where('history.0.action', 'Reopened')
        ->where('history.0.record', 'Timesheet')
        ->where('history.1.record', 'Office session')
        ->where('history.1.changed_by', 'Zara Admin')
        ->where('history.1.changes.0', ['field' => 'Clocked in at', 'from' => '2026-11-17 08:00', 'to' => '2026-11-17 08:30'])
    );
});
