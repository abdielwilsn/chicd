<?php

use App\Models\Office;
use App\Models\OfficeAttendance;
use App\Models\User;

/**
 * The test office sits at 5.0377, 7.9128 with a 100 m geofence. Moving
 * 0.0023° north is about 256 m away, well outside it.
 */
function officeLocation(): array
{
    return ['latitude' => 5.0377, 'longitude' => 7.9128, 'accuracy' => 12.4];
}

function locationOutsideOffice(): array
{
    return ['latitude' => 5.0400, 'longitude' => 7.9128, 'accuracy' => 30];
}

test('guests cannot clock in', function () {
    $response = $this->post(route('office-attendances.store'), officeLocation());

    $response->assertRedirect(route('login'));
    $this->assertDatabaseEmpty('office_attendances');
});

test('staff clock in at the office with the server time and their location', function () {
    $this->travelTo('2026-11-18 07:02:00');
    $office = Office::factory()->create(['latitude' => 5.0377, 'longitude' => 7.9128, 'geofence_radius_metres' => 100]);
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('office-attendances.store'), officeLocation());

    $response->assertSessionHasNoErrors()->assertRedirect(route('dashboard'));
    $attendance = OfficeAttendance::sole();
    expect($attendance->user_id)->toBe($user->id)
        ->and($attendance->office_id)->toBe($office->id)
        ->and($attendance->clocked_in_at->toDateTimeString())->toBe('2026-11-18 07:02:00')
        ->and($attendance->clock_in_latitude)->toBe(5.0377)
        ->and($attendance->clock_in_longitude)->toBe(7.9128)
        ->and($attendance->clock_in_accuracy_metres)->toBe(12)
        ->and($attendance->clock_in_distance_metres)->toBe(0)
        ->and($attendance->clocked_in_within_geofence)->toBeTrue()
        ->and($attendance->clocked_out_at)->toBeNull();
});

test('a clock-in outside the geofence is recorded and flagged, not refused', function () {
    Office::factory()->create(['latitude' => 5.0377, 'longitude' => 7.9128, 'geofence_radius_metres' => 100]);
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('office-attendances.store'), locationOutsideOffice());

    $response->assertSessionHasNoErrors()->assertRedirect(route('dashboard'));
    $attendance = OfficeAttendance::sole();
    expect($attendance->clocked_in_within_geofence)->toBeFalse()
        ->and($attendance->clock_in_distance_metres)->toBe(256);
});

test('a clock-in is checked against the nearest office', function () {
    Office::factory()->create(['latitude' => 6.4550, 'longitude' => 3.3841]);
    $uyo = Office::factory()->create(['latitude' => 5.0377, 'longitude' => 7.9128]);

    $this->actingAs(User::factory()->create())->post(route('office-attendances.store'), officeLocation());

    expect(OfficeAttendance::sole()->office_id)->toBe($uyo->id);
});

test('a clock-in is flagged when no office has been set up', function () {
    $this->actingAs(User::factory()->create())->post(route('office-attendances.store'), officeLocation());

    $attendance = OfficeAttendance::sole();
    expect($attendance->office_id)->toBeNull()
        ->and($attendance->clocked_in_within_geofence)->toBeFalse();
});

test('staff cannot clock in twice without clocking out', function () {
    $user = User::factory()->create();
    OfficeAttendance::factory()->for($user)->create();

    $response = $this->actingAs($user)->post(route('office-attendances.store'), officeLocation());

    $response->assertSessionHasErrors(['attendance' => 'You are already clocked in. Clock out before clocking in again.']);
    expect(OfficeAttendance::count())->toBe(1);
});

test('a clock-in without a location is rejected with a message about location access', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('office-attendances.store'), []);

    $response->assertSessionHasErrors([
        'latitude' => 'We could not read your location. Please allow location access and try again.',
        'longitude' => 'We could not read your location. Please allow location access and try again.',
    ]);
    $this->assertDatabaseEmpty('office_attendances');
});

test('a clock-in with impossible coordinates is rejected', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('office-attendances.store'), ['latitude' => 91, 'longitude' => 181]);

    $response->assertSessionHasErrors(['latitude', 'longitude']);
    $this->assertDatabaseEmpty('office_attendances');
});

test('staff clock out of their open session against the office they clocked in at', function () {
    $this->travelTo('2026-11-18 16:15:00');
    $office = Office::factory()->create(['latitude' => 5.0377, 'longitude' => 7.9128, 'geofence_radius_metres' => 100]);
    $user = User::factory()->create();
    $attendance = OfficeAttendance::factory()->for($user)->for($office)->create(['clocked_in_at' => '2026-11-18 07:02:00']);

    $response = $this->actingAs($user)->patch(route('office-attendances.update', $attendance), locationOutsideOffice());

    $response->assertSessionHasNoErrors()->assertRedirect(route('dashboard'));
    $attendance->refresh();
    expect($attendance->clocked_out_at->toDateTimeString())->toBe('2026-11-18 16:15:00')
        ->and($attendance->clock_out_latitude)->toBe(5.04)
        ->and($attendance->clock_out_accuracy_metres)->toBe(30)
        ->and($attendance->clock_out_distance_metres)->toBe(256)
        ->and($attendance->clocked_out_within_geofence)->toBeFalse()
        ->and($attendance->clocked_in_at->toDateTimeString())->toBe('2026-11-18 07:02:00');
});

test('staff cannot clock out of a session that is already closed', function () {
    $user = User::factory()->create();
    $attendance = OfficeAttendance::factory()->for($user)->clockedOut()->create(['clocked_out_at' => '2026-11-18 16:00:00']);

    $response = $this->actingAs($user)->patch(route('office-attendances.update', $attendance), officeLocation());

    $response->assertSessionHasErrors(['attendance' => 'You have already clocked out of this session.']);
    expect($attendance->refresh()->clocked_out_at->toDateTimeString())->toBe('2026-11-18 16:00:00');
});

test('staff cannot clock out of another person\'s session', function () {
    $attendance = OfficeAttendance::factory()->create();

    $response = $this->actingAs(User::factory()->create())->patch(route('office-attendances.update', $attendance), officeLocation());

    $response->assertNotFound();
    expect($attendance->refresh()->clocked_out_at)->toBeNull();
});
