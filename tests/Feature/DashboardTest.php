<?php

use App\Models\FieldActivity;
use App\Models\Office;
use App\Models\OfficeAttendance;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('the dashboard shows what the staff member is doing now and has recorded today', function () {
    $this->travelTo('2026-11-18 14:00:00');
    config(['chicd.field_programmes' => ['Show Up Ready to Learn (SURL)']]);
    $user = User::factory()->create();
    $office = Office::factory()->create(['name' => 'Uyo office']);
    $openSession = OfficeAttendance::factory()->for($user)->for($office)->create([
        'clocked_in_at' => '2026-11-18 07:05:00',
        'clocked_in_within_geofence' => false,
    ]);
    $endedActivity = FieldActivity::factory()->for($user)->ended()->create([
        'started_at' => '2026-11-18 09:00:00',
        'ended_at' => '2026-11-18 10:30:00',
    ]);
    $runningActivity = FieldActivity::factory()->for($user)->create(['started_at' => '2026-11-18 11:00:00']);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Dashboard')
        ->where('today', 'Wednesday 18 November 2026')
        ->where('openOfficeAttendance.id', $openSession->id)
        ->where('openOfficeAttendance.office', 'Uyo office')
        ->where('openOfficeAttendance.clocked_in_time', '08:05')
        ->where('openOfficeAttendance.clocked_in_within_geofence', false)
        ->where('openFieldActivity.id', $runningActivity->id)
        ->has('officeAttendances', 1)
        ->has('fieldActivities', 2)
        ->where('fieldActivities.0.id', $endedActivity->id)
        ->where('fieldActivities.0.minutes', 90)
        ->where('fieldActivities.1.id', $runningActivity->id)
        ->where('programmes', ['Show Up Ready to Learn (SURL)'])
    );
});

test('the dashboard lists only today\'s records in Nigerian time, and only the staff member\'s own', function () {
    $this->travelTo('2026-11-18 14:00:00');
    $user = User::factory()->create();
    $afterMidnightLagos = OfficeAttendance::factory()->for($user)->clockedOut()->create(['clocked_in_at' => '2026-11-17 23:30:00']);
    OfficeAttendance::factory()->for($user)->clockedOut()->create(['clocked_in_at' => '2026-11-17 22:30:00']);
    OfficeAttendance::factory()->clockedOut()->create(['clocked_in_at' => '2026-11-18 07:00:00']);
    FieldActivity::factory()->create(['started_at' => '2026-11-18 09:00:00']);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertInertia(fn (Assert $page) => $page
        ->has('officeAttendances', 1)
        ->where('officeAttendances.0.id', $afterMidnightLagos->id)
        ->has('fieldActivities', 0)
        ->where('openOfficeAttendance', null)
        ->where('openFieldActivity', null)
    );
});

test('a session left open on an earlier day is still shown so it can be clocked out', function () {
    $this->travelTo('2026-11-18 08:00:00');
    $user = User::factory()->create();
    $forgotten = OfficeAttendance::factory()->for($user)->create(['clocked_in_at' => '2026-11-17 07:00:00']);

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('openOfficeAttendance.id', $forgotten->id)
        ->where('openOfficeAttendance.date', 'Tue 17 Nov 2026')
        ->has('officeAttendances', 0)
    );
});
