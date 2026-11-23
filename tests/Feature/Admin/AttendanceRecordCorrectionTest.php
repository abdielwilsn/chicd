<?php

use App\Enums\CorrectionStatus;
use App\Models\CorrectionRequest;
use App\Models\FieldActivity;
use App\Models\OfficeAttendance;
use App\Models\RecordChange;
use App\Models\Timesheet;
use App\Models\User;

beforeEach(function () {
    $this->travelTo('2026-11-20 10:00:00');
    $this->admin = User::factory()->admin()->create();
    $this->staff = User::factory()->create();
});

test('staff cannot correct records through the admin routes', function () {
    $attendance = OfficeAttendance::factory()->for($this->staff)->create();

    $this->actingAs($this->staff)
        ->patch(route('admin.office-attendances.update', $attendance), ['clocked_in_at' => '2026-11-20T08:00', 'reason' => 'x'])
        ->assertForbidden();
});

test('an admin adds a forgotten clock-out, answering the staff request, with the change in the audit trail', function () {
    $attendance = OfficeAttendance::factory()->for($this->staff)->create(['clocked_in_at' => '2026-11-18 07:00:00']);
    $correction = CorrectionRequest::factory()->for($this->staff)->create(['work_date' => '2026-11-18']);
    $correction->correctable()->associate($attendance)->save();

    $this->actingAs($this->admin)->patch(route('admin.office-attendances.update', $attendance), [
        'clocked_in_at' => '2026-11-18T08:00',
        'clocked_out_at' => '2026-11-18T17:00',
        'reason' => 'Forgot to clock out; confirmed with line manager.',
        'correction_request_id' => $correction->id,
    ])->assertRedirect()->assertSessionHasNoErrors();

    $attendance->refresh();
    expect($attendance->clocked_out_at->toDateTimeString())->toBe('2026-11-18 16:00:00')
        ->and($attendance->clock_out_latitude)->toBeNull();

    $change = RecordChange::sole();
    expect($change->auditable->is($attendance))->toBeTrue()
        ->and($change->changed_by)->toBe($this->admin->id)
        ->and($change->correction_request_id)->toBe($correction->id)
        ->and($change->changes)->toBe(['clocked_out_at' => ['from' => null, 'to' => '2026-11-18 17:00']]);

    $correction->refresh();
    expect($correction->status)->toBe(CorrectionStatus::Resolved)
        ->and($correction->resolution_note)->toBe('Forgot to clock out; confirmed with line manager.');
});

test('an admin corrects a field activity\'s details', function () {
    $activity = FieldActivity::factory()->for($this->staff)->ended()->create([
        'programme' => 'Show Up Ready to Learn (SURL)',
        'activity' => 'Uniform distribution',
        'location' => 'Eket',
        'description' => 'Distributed 40 uniforms.',
        'started_at' => '2026-11-18 08:00:00',
        'ended_at' => '2026-11-18 10:00:00',
    ]);

    $this->actingAs($this->admin)->patch(route('admin.field-activities.update', $activity), [
        'started_at' => '2026-11-18T09:00',
        'ended_at' => '2026-11-18T11:00',
        'programme' => 'Safe Housing Programme for Vulnerable Adults',
        'activity' => 'Uniform distribution',
        'location' => 'Eket',
        'description' => 'Distributed 40 uniforms.',
        'reason' => 'Wrong programme chosen.',
    ])->assertSessionHasNoErrors();

    expect($activity->fresh()->programme)->toBe('Safe Housing Programme for Vulnerable Adults')
        ->and(RecordChange::sole()->changes)->toBe([
            'programme' => ['from' => 'Show Up Ready to Learn (SURL)', 'to' => 'Safe Housing Programme for Vulnerable Adults'],
        ]);
});

test('records in an approved month cannot be corrected until it is reopened', function () {
    $attendance = OfficeAttendance::factory()->for($this->staff)->clockedOut()->create(['clocked_in_at' => '2026-10-14 07:00:00', 'clocked_out_at' => '2026-10-14 15:00:00']);
    Timesheet::factory()->for($this->staff)->approved()->create(['month' => '2026-10-01']);

    $this->actingAs($this->admin)->patch(route('admin.office-attendances.update', $attendance), [
        'clocked_in_at' => '2026-10-14T07:30',
        'clocked_out_at' => '2026-10-14T16:00',
        'reason' => 'Late start.',
    ])->assertSessionHasErrors('record');

    expect(RecordChange::count())->toBe(0);
});

test('a record cannot be moved into an approved month', function () {
    $attendance = OfficeAttendance::factory()->for($this->staff)->clockedOut()->create(['clocked_in_at' => '2026-11-01 07:00:00', 'clocked_out_at' => '2026-11-01 15:00:00']);
    Timesheet::factory()->for($this->staff)->approved()->create(['month' => '2026-10-01']);

    $this->actingAs($this->admin)->patch(route('admin.office-attendances.update', $attendance), [
        'clocked_in_at' => '2026-10-31T08:00',
        'clocked_out_at' => '2026-10-31T16:00',
        'reason' => 'Wrong day.',
    ])->assertSessionHasErrors('record');
});

test('corrected times must be in order and not in the future', function () {
    $attendance = OfficeAttendance::factory()->for($this->staff)->create(['clocked_in_at' => '2026-11-20 07:00:00']);

    $this->actingAs($this->admin)->patch(route('admin.office-attendances.update', $attendance), [
        'clocked_in_at' => '2026-11-20T08:00',
        'clocked_out_at' => '2026-11-20T07:00',
        'reason' => 'x',
    ])->assertSessionHasErrors('clocked_out_at');

    $this->actingAs($this->admin)->patch(route('admin.office-attendances.update', $attendance), [
        'clocked_in_at' => '2026-11-20T08:00',
        'clocked_out_at' => '2026-11-20T18:00',
        'reason' => 'x',
    ])->assertSessionHasErrors(['clocked_out_at' => 'The time cannot be in the future.']);
});

test('a correction request from another staff member cannot be answered with this record', function () {
    $attendance = OfficeAttendance::factory()->for($this->staff)->create(['clocked_in_at' => '2026-11-18 07:00:00']);
    $otherRequest = CorrectionRequest::factory()->create();

    $this->actingAs($this->admin)->patch(route('admin.office-attendances.update', $attendance), [
        'clocked_in_at' => '2026-11-18T08:00',
        'clocked_out_at' => '2026-11-18T16:00',
        'reason' => 'x',
        'correction_request_id' => $otherRequest->id,
    ])->assertSessionHasErrors('correction_request_id');
});

test('saving without changes leaves no audit entry', function () {
    $attendance = OfficeAttendance::factory()->for($this->staff)->create(['clocked_in_at' => '2026-11-18 07:00:00']);

    $this->actingAs($this->admin)->patch(route('admin.office-attendances.update', $attendance), [
        'clocked_in_at' => '2026-11-18T08:00',
        'reason' => 'Checking.',
    ])->assertSessionHasNoErrors();

    expect(RecordChange::count())->toBe(0);
});
