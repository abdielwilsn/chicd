<?php

use App\Enums\LeaveStatus;
use App\Models\LeaveRequest;
use App\Models\PublicHoliday;
use App\Models\Timesheet;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->travelTo('2026-12-05 10:00:00');
    $this->admin = User::factory()->admin()->create(['created_at' => '2026-10-01 09:00:00']);
    $this->staff = User::factory()->create(['name' => 'Ada Okon', 'created_at' => '2026-10-01 09:00:00']);
});

test('staff cannot decide leave', function () {
    $leave = LeaveRequest::factory()->for($this->staff)->create();

    $this->actingAs($this->staff)->patch(route('admin.leave.update', $leave), ['status' => 'approved'])->assertForbidden();

    expect($leave->refresh()->status)->toBe(LeaveStatus::Pending);
});

test('admins see leave waiting for a decision, oldest first', function () {
    $newer = LeaveRequest::factory()->for($this->staff)->create(['created_at' => '2026-12-04 09:00:00']);
    $older = LeaveRequest::factory()->for($this->staff)->create(['created_at' => '2026-12-01 09:00:00', 'starts_on' => '2027-01-04', 'ends_on' => '2027-01-04']);

    $this->actingAs($this->admin)->get(route('admin.leave.index'))->assertInertia(fn (Assert $page) => $page
        ->component('admin/leave/Index')
        ->where('pending.0.id', $older->id)
        ->where('pending.1.id', $newer->id)
        ->where('pending.0.staff_name', 'Ada Okon')
    );
});

test('approved leave shows on the attendance overview as leave instead of absence', function () {
    $leave = LeaveRequest::factory()->for($this->staff)->create(['starts_on' => '2026-11-02', 'ends_on' => '2026-11-06']);

    $this->actingAs($this->admin)->patch(route('admin.leave.update', $leave), ['status' => 'approved'])->assertSessionHasNoErrors();

    $leave->refresh();
    expect($leave->status)->toBe(LeaveStatus::Approved)
        ->and($leave->decided_by)->toBe($this->admin->id);

    $this->actingAs($this->admin)->get(route('admin.attendance.index', ['month' => '2026-11']))->assertInertia(fn (Assert $page) => $page
        ->where('staff', fn ($staff) => collect($staff)->firstWhere('id', $this->staff->id)['attendance'] === [
            'workingDays' => 21, 'lateDays' => 0, 'absentDays' => 16, 'leaveDays' => 5,
        ])
    );
});

test('declining leave needs a note for the staff member', function () {
    $leave = LeaveRequest::factory()->for($this->staff)->create();

    $this->actingAs($this->admin)->patch(route('admin.leave.update', $leave), ['status' => 'declined'])
        ->assertSessionHasErrors(['note' => 'Please tell the staff member why their leave was declined.']);

    expect($leave->refresh()->status)->toBe(LeaveStatus::Pending);
});

test('leave cannot be approved into a month whose timesheet is approved', function () {
    Timesheet::factory()->for($this->staff)->approved()->create(['month' => '2026-11-01']);
    $leave = LeaveRequest::factory()->for($this->staff)->create(['starts_on' => '2026-11-30', 'ends_on' => '2026-12-02']);

    $this->actingAs($this->admin)->patch(route('admin.leave.update', $leave), ['status' => 'approved'])
        ->assertSessionHasErrors(['status' => 'The timesheet for this month is already approved. Reopen it before approving this leave.']);

    expect($leave->refresh()->status)->toBe(LeaveStatus::Pending);
});

test('a decided request cannot be decided again', function () {
    $leave = LeaveRequest::factory()->for($this->staff)->approved()->create();

    $this->actingAs($this->admin)->patch(route('admin.leave.update', $leave), ['status' => 'declined', 'note' => 'Changed my mind'])
        ->assertConflict();
});

test('admins add and remove public holidays', function () {
    $this->actingAs($this->admin)->post(route('admin.holidays.store'), ['date' => '2026-12-25', 'name' => 'Christmas Day'])
        ->assertSessionHasNoErrors();

    $holiday = PublicHoliday::sole();
    expect($holiday->date->toDateString())->toBe('2026-12-25');

    $this->actingAs($this->admin)->post(route('admin.holidays.store'), ['date' => '2026-12-25', 'name' => 'Again'])
        ->assertSessionHasErrors(['date' => 'This day is already a public holiday.']);

    $this->actingAs($this->admin)->delete(route('admin.holidays.destroy', $holiday))->assertRedirect();
    $this->assertModelMissing($holiday);
});
