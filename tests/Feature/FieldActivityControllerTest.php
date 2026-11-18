<?php

use App\Models\FieldActivity;
use App\Models\User;

beforeEach(function () {
    config(['chicd.field_programmes' => ['Show Up Ready to Learn (SURL)', 'Safe Housing Programme for Vulnerable Adults']]);
});

function fieldActivityDetails(array $overrides = []): array
{
    return [
        'programme' => 'Show Up Ready to Learn (SURL)',
        'activity' => 'School uniform distribution',
        'description' => 'Uniforms for primary 1 to 3.',
        'location' => 'Ikot Ekpene',
        'latitude' => 5.1794,
        'longitude' => 7.7144,
        'accuracy' => 18.6,
        ...$overrides,
    ];
}

test('guests cannot start a field activity', function () {
    $response = $this->post(route('field-activities.store'), fieldActivityDetails());

    $response->assertRedirect(route('login'));
    $this->assertDatabaseEmpty('field_activities');
});

test('staff start a field activity with the server time and their location', function () {
    $this->travelTo('2026-11-18 09:10:00');
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('field-activities.store'), fieldActivityDetails());

    $response->assertSessionHasNoErrors()->assertRedirect(route('dashboard'));
    $activity = FieldActivity::sole();
    expect($activity->user_id)->toBe($user->id)
        ->and($activity->programme)->toBe('Show Up Ready to Learn (SURL)')
        ->and($activity->activity)->toBe('School uniform distribution')
        ->and($activity->description)->toBe('Uniforms for primary 1 to 3.')
        ->and($activity->location)->toBe('Ikot Ekpene')
        ->and($activity->started_at->toDateTimeString())->toBe('2026-11-18 09:10:00')
        ->and($activity->start_latitude)->toBe(5.1794)
        ->and($activity->start_longitude)->toBe(7.7144)
        ->and($activity->start_accuracy_metres)->toBe(19)
        ->and($activity->ended_at)->toBeNull();
});

test('staff can record another activity once the previous one has ended', function () {
    $user = User::factory()->create();
    FieldActivity::factory()->for($user)->ended()->create();

    $response = $this->actingAs($user)->post(route('field-activities.store'), fieldActivityDetails(['activity' => 'Parent meeting']));

    $response->assertSessionHasNoErrors();
    expect($user->fieldActivities()->count())->toBe(2);
});

test('staff cannot start an activity while another is still running', function () {
    $user = User::factory()->create();
    FieldActivity::factory()->for($user)->create();

    $response = $this->actingAs($user)->post(route('field-activities.store'), fieldActivityDetails());

    $response->assertSessionHasErrors(['field_activity' => 'Please end your current activity before starting another.']);
    expect(FieldActivity::count())->toBe(1);
});

test('a field activity without the required details is rejected', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('field-activities.store'), []);

    $response->assertSessionHasErrors([
        'programme',
        'activity',
        'location' => 'Please say where the activity is taking place.',
        'latitude' => 'We could not read your location. Please allow location access and try again.',
        'longitude',
    ]);
    $this->assertDatabaseEmpty('field_activities');
});

test('a field activity for a programme that is not on the list is rejected', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('field-activities.store'), fieldActivityDetails(['programme' => 'Made-up programme']));

    $response->assertSessionHasErrors(['programme' => 'Please choose a programme from the list.']);
    $this->assertDatabaseEmpty('field_activities');
});

test('staff end their running activity with the server time and their location', function () {
    $this->travelTo('2026-11-18 12:40:00');
    $user = User::factory()->create();
    $activity = FieldActivity::factory()->for($user)->create(['started_at' => '2026-11-18 09:10:00']);

    $response = $this->actingAs($user)->patch(route('field-activities.update', $activity), [
        'latitude' => 5.1801,
        'longitude' => 7.7150,
        'accuracy' => 9,
    ]);

    $response->assertSessionHasNoErrors()->assertRedirect(route('dashboard'));
    $activity->refresh();
    expect($activity->ended_at->toDateTimeString())->toBe('2026-11-18 12:40:00')
        ->and($activity->end_latitude)->toBe(5.1801)
        ->and($activity->end_longitude)->toBe(7.715)
        ->and($activity->end_accuracy_metres)->toBe(9)
        ->and($activity->started_at->toDateTimeString())->toBe('2026-11-18 09:10:00');
});

test('staff cannot end an activity that has already ended', function () {
    $user = User::factory()->create();
    $activity = FieldActivity::factory()->for($user)->ended()->create(['ended_at' => '2026-11-18 12:00:00']);

    $response = $this->actingAs($user)->patch(route('field-activities.update', $activity), ['latitude' => 5.18, 'longitude' => 7.71]);

    $response->assertSessionHasErrors(['field_activity' => 'This activity has already ended.']);
    expect($activity->refresh()->ended_at->toDateTimeString())->toBe('2026-11-18 12:00:00');
});

test('staff cannot end another person\'s activity', function () {
    $activity = FieldActivity::factory()->create();

    $response = $this->actingAs(User::factory()->create())->patch(route('field-activities.update', $activity), ['latitude' => 5.18, 'longitude' => 7.71]);

    $response->assertNotFound();
    expect($activity->refresh()->ended_at)->toBeNull();
});
