<?php

use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Models\LeaveRequest;
use App\Models\Timesheet;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->travelTo('2026-11-23 10:00:00');
    $this->staff = User::factory()->create();
});

test('guests cannot request leave', function () {
    $this->post(route('leave.store'), [])->assertRedirect(route('login'));
});

test('staff request leave, which waits for an admin', function () {
    $this->actingAs($this->staff)->post(route('leave.store'), [
        'type' => 'annual',
        'starts_on' => '2026-12-07',
        'ends_on' => '2026-12-11',
        'reason' => 'Family visit',
    ])->assertSessionHasNoErrors()->assertRedirect();

    $leave = LeaveRequest::sole();
    expect($leave->user_id)->toBe($this->staff->id)
        ->and($leave->type)->toBe(LeaveType::Annual)
        ->and($leave->starts_on->toDateString())->toBe('2026-12-07')
        ->and($leave->ends_on->toDateString())->toBe('2026-12-11')
        ->and($leave->status)->toBe(LeaveStatus::Pending);

    $this->actingAs($this->staff)->get(route('leave.index'))->assertInertia(fn (Assert $page) => $page
        ->component('leave/Index')
        ->where('leaveRequests.0.dates', 'Mon 7 Dec – Fri 11 Dec 2026')
        ->where('leaveRequests.0.status', 'pending')
    );
});

test('leave cannot end before it starts', function () {
    $this->actingAs($this->staff)->post(route('leave.store'), [
        'type' => 'annual',
        'starts_on' => '2026-12-11',
        'ends_on' => '2026-12-07',
    ])->assertSessionHasErrors(['ends_on' => 'The last day of leave cannot be before the first day.']);
});

test('leave cannot overlap leave already requested or approved', function () {
    LeaveRequest::factory()->for($this->staff)->approved()->create(['starts_on' => '2026-12-07', 'ends_on' => '2026-12-11']);

    $this->actingAs($this->staff)->post(route('leave.store'), [
        'type' => 'sick',
        'starts_on' => '2026-12-11',
        'ends_on' => '2026-12-14',
    ])->assertSessionHasErrors(['starts_on' => 'You already have leave requested or approved for some of these days.']);
});

test('leave cannot be requested in a month whose timesheet is approved', function () {
    Timesheet::factory()->for($this->staff)->approved()->create(['month' => '2026-10-01']);

    $this->actingAs($this->staff)->post(route('leave.store'), [
        'type' => 'sick',
        'starts_on' => '2026-10-28',
        'ends_on' => '2026-11-02',
    ])->assertSessionHasErrors(['starts_on' => 'Your timesheet for this month is already approved. Ask an admin to reopen it first.']);
});

test('staff cancel a request that has not been decided', function () {
    $leave = LeaveRequest::factory()->for($this->staff)->create();

    $this->actingAs($this->staff)->delete(route('leave.cancel', $leave))->assertRedirect();

    expect($leave->refresh()->status)->toBe(LeaveStatus::Cancelled);
});

test('staff cannot cancel leave that has been decided', function () {
    $leave = LeaveRequest::factory()->for($this->staff)->approved()->create();

    $this->actingAs($this->staff)->delete(route('leave.cancel', $leave))->assertConflict();

    expect($leave->refresh()->status)->toBe(LeaveStatus::Approved);
});

test('staff cannot cancel another person\'s leave', function () {
    $leave = LeaveRequest::factory()->create();

    $this->actingAs($this->staff)->delete(route('leave.cancel', $leave))->assertNotFound();

    expect($leave->refresh()->status)->toBe(LeaveStatus::Pending);
});
