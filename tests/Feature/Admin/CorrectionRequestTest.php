<?php

use App\Enums\CorrectionStatus;
use App\Models\CorrectionRequest;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('admins see waiting requests oldest first and recently closed ones', function () {
    $this->travelTo('2026-11-20 10:00:00');
    $admin = User::factory()->admin()->create();
    $newer = CorrectionRequest::factory()->create(['created_at' => '2026-11-19 09:00:00']);
    $older = CorrectionRequest::factory()->create(['created_at' => '2026-11-18 09:00:00']);
    $closed = CorrectionRequest::factory()->create(['status' => CorrectionStatus::Declined, 'resolved_at' => now(), 'resolution_note' => 'No']);

    $this->actingAs($admin)->get(route('admin.corrections.index'))->assertInertia(fn (Assert $page) => $page
        ->component('admin/corrections/Index')
        ->where('pending.0.id', $older->id)
        ->where('pending.1.id', $newer->id)
        ->where('pending.0.staff_name', $older->user->name)
        ->where('recentlyClosed.0.id', $closed->id)
    );
});

test('an admin can decline a request with a note for the staff member', function () {
    $admin = User::factory()->admin()->create();
    $request = CorrectionRequest::factory()->create();

    $this->actingAs($admin)->patch(route('admin.corrections.update', $request), [
        'status' => 'declined',
        'note' => 'The clock-in time is correct.',
    ])->assertSessionHasNoErrors();

    $request->refresh();
    expect($request->status)->toBe(CorrectionStatus::Declined)
        ->and($request->resolved_by)->toBe($admin->id)
        ->and($request->resolution_note)->toBe('The clock-in time is correct.');
});

test('a closed request cannot be closed again', function () {
    $request = CorrectionRequest::factory()->create(['status' => CorrectionStatus::Resolved]);

    $this->actingAs(User::factory()->admin()->create())
        ->patch(route('admin.corrections.update', $request), ['status' => 'declined', 'note' => 'x'])
        ->assertConflict();
});

test('a note is required to close a request', function () {
    $request = CorrectionRequest::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->patch(route('admin.corrections.update', $request), ['status' => 'resolved', 'note' => ''])
        ->assertSessionHasErrors('note');
});
