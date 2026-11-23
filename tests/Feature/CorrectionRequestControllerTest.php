<?php

use App\Enums\CorrectionStatus;
use App\Models\CorrectionRequest;
use App\Models\FieldActivity;
use App\Models\OfficeAttendance;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests cannot request corrections', function () {
    $this->post(route('attendance.corrections.store'), ['details' => 'x'])->assertRedirect(route('login'));
});

test('staff can request a correction to their own office session, dated by the session in Nigerian time', function () {
    $this->travelTo('2026-11-20 10:00:00');
    $user = User::factory()->create();
    $attendance = OfficeAttendance::factory()->for($user)->create(['clocked_in_at' => '2026-11-17 23:30:00']);

    $response = $this->actingAs($user)->post(route('attendance.corrections.store'), [
        'record_type' => 'office_attendance',
        'record_id' => $attendance->id,
        'details' => 'I forgot to clock out. I left at 17:00.',
    ]);

    $response->assertRedirect()->assertSessionHasNoErrors();

    $request = CorrectionRequest::sole();
    expect($request->user_id)->toBe($user->id)
        ->and($request->correctable->is($attendance))->toBeTrue()
        ->and($request->work_date->toDateString())->toBe('2026-11-18')
        ->and($request->status)->toBe(CorrectionStatus::Pending);
});

test('staff can request a correction to their own field activity', function () {
    $user = User::factory()->create();
    $activity = FieldActivity::factory()->for($user)->ended()->create();

    $this->actingAs($user)->post(route('attendance.corrections.store'), [
        'record_type' => 'field_activity',
        'record_id' => $activity->id,
        'details' => 'Wrong programme chosen.',
    ])->assertSessionHasNoErrors();

    expect(CorrectionRequest::sole()->correctable->is($activity))->toBeTrue();
});

test('staff can report a day with nothing recorded', function () {
    $this->travelTo('2026-11-20 10:00:00');
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('attendance.corrections.store'), [
        'work_date' => '2026-11-19',
        'details' => 'My phone died, I was in the office 08:00 to 16:00.',
    ])->assertSessionHasNoErrors();

    $request = CorrectionRequest::sole();
    expect($request->correctable_type)->toBeNull()
        ->and($request->work_date->toDateString())->toBe('2026-11-19');
});

test('another staff member\'s record cannot be named', function () {
    $user = User::factory()->create();
    $someoneElses = OfficeAttendance::factory()->create();

    $this->actingAs($user)->post(route('attendance.corrections.store'), [
        'record_type' => 'office_attendance',
        'record_id' => $someoneElses->id,
        'details' => 'Please fix.',
    ])->assertSessionHasErrors('record_id');

    expect(CorrectionRequest::count())->toBe(0);
});

test('a correction request needs details and a day that is not in the future', function () {
    $this->travelTo('2026-11-20 10:00:00');

    $this->actingAs(User::factory()->create())->post(route('attendance.corrections.store'), [
        'work_date' => '2026-11-21',
        'details' => '',
    ])->assertSessionHasErrors(['work_date', 'details']);
});

test('the attendance page lists the month\'s correction requests and timesheet status', function () {
    $this->travelTo('2026-11-20 10:00:00');
    $user = User::factory()->create();
    $attendance = OfficeAttendance::factory()->for($user)->clockedOut()->create([
        'clocked_in_at' => '2026-11-17 07:00:00',
        'clocked_out_at' => '2026-11-17 15:00:00',
    ]);
    $request = CorrectionRequest::factory()->for($user)->create(['work_date' => '2026-11-17']);
    $request->correctable()->associate($attendance)->save();
    CorrectionRequest::factory()->for($user)->create(['work_date' => '2026-10-02']);
    CorrectionRequest::factory()->create(['work_date' => '2026-11-17']);

    $response = $this->actingAs($user)->get(route('attendance.index'));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('timesheet.status', 'pending')
        ->where('timesheet.approvedAt', null)
        ->has('correctionRequests', 1)
        ->where('correctionRequests.0.id', $request->id)
        ->where('correctionRequests.0.record', 'Office session from 08:00 to 16:00')
        ->where('correctionRequests.0.status', 'pending')
    );
});
