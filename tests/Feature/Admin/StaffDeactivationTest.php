<?php

use App\Http\Middleware\EnsureAccountIsActive;
use App\Mail\StaffInvitation;
use App\Models\OfficeAttendance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create(['name' => 'Zara Admin']);
});

test('an admin deactivates a staff member who has left, who is signed out everywhere and listed as former staff', function () {
    config(['session.driver' => 'database']);
    $this->travelTo('2026-11-23 10:00:00');
    $leaver = User::factory()->create(['name' => 'Ada Okon']);
    DB::table('sessions')->insert(['id' => 'leaver-session', 'user_id' => $leaver->id, 'payload' => '', 'last_activity' => now()->timestamp]);

    $this->actingAs($this->admin)->post(route('admin.staff.deactivate', $leaver))
        ->assertRedirect()
        ->assertInertiaFlash('toast.message', 'Ada Okon can no longer sign in. Their records have been kept.');

    $leaver->refresh();
    expect($leaver->isActive())->toBeFalse()
        ->and($leaver->deactivated_by)->toBe($this->admin->id);
    $this->assertDatabaseMissing('sessions', ['id' => 'leaver-session']);

    $this->actingAs($this->admin)->get(route('admin.staff.index'))->assertInertia(fn (Assert $page) => $page
        ->has('staff', 1)
        ->where('formerStaff.0.id', $leaver->id)
        ->where('formerStaff.0.deactivated_on', '23 Nov 2026')
        ->where('formerStaff.0.deactivated_by', 'Zara Admin')
    );
});

test('deactivating someone who left a clock-in open asks the admin to close it', function () {
    $leaver = User::factory()->create(['name' => 'Ada Okon']);
    OfficeAttendance::factory()->for($leaver)->create(['clocked_out_at' => null]);

    $this->actingAs($this->admin)->post(route('admin.staff.deactivate', $leaver))
        ->assertInertiaFlash('toast.type', 'warning')
        ->assertInertiaFlash('toast.message', 'Ada Okon can no longer sign in. They left a clock-in or activity open: close it on their timesheet.');
});

test('an admin cannot deactivate their own account', function () {
    $this->actingAs($this->admin)->post(route('admin.staff.deactivate', $this->admin))
        ->assertSessionHasErrors(['account' => 'You cannot deactivate your own account. Ask another admin to do it.']);

    expect($this->admin->refresh()->isActive())->toBeTrue();
});

test('staff cannot deactivate accounts', function () {
    $colleague = User::factory()->create();

    $this->actingAs(User::factory()->create())->post(route('admin.staff.deactivate', $colleague))->assertForbidden();

    expect($colleague->refresh()->isActive())->toBeTrue();
});

test('a deactivated staff member who is still signed in is signed out on their next request', function () {
    $leaver = User::factory()->create(['deactivated_at' => now()]);

    $this->actingAs($leaver)->get(route('dashboard'))
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors(['email' => EnsureAccountIsActive::MESSAGE]);

    $this->assertGuest();
});

test('a deactivated admin loses access to the admin pages', function () {
    $formerAdmin = User::factory()->admin()->create(['deactivated_at' => now()]);

    $this->actingAs($formerAdmin)->get(route('admin.staff.index'))->assertRedirect(route('login'));

    $this->assertGuest();
});

test('a deactivated staff member cannot sign in, and is told why', function () {
    User::factory()->create(['email' => 'ada@example.com', 'deactivated_at' => now()]);

    $this->post(route('login.store'), ['email' => 'ada@example.com', 'password' => 'password'])
        ->assertSessionHasErrors(['email' => EnsureAccountIsActive::MESSAGE]);

    $this->assertGuest();
});

test('a wrong password for a deactivated account gets the usual message, so it does not reveal the account', function () {
    User::factory()->create(['email' => 'ada@example.com', 'deactivated_at' => now()]);

    $this->post(route('login.store'), ['email' => 'ada@example.com', 'password' => 'wrong-password'])
        ->assertSessionHasErrors(['email' => 'These credentials do not match our records.']);
});

test('a reactivated staff member can sign in again', function () {
    $returner = User::factory()->create(['email' => 'ada@example.com', 'deactivated_at' => now(), 'deactivated_by' => $this->admin->id]);

    $this->actingAs($this->admin)->delete(route('admin.staff.reactivate', $returner))->assertRedirect();
    auth()->logout();

    expect($returner->refresh()->deactivated_by)->toBeNull();
    $this->post(route('login.store'), ['email' => 'ada@example.com', 'password' => 'password'])->assertSessionHasNoErrors();
    $this->assertAuthenticatedAs($returner);
});

test('a deactivated account is not sent a password link', function () {
    Mail::fake();
    $leaver = User::factory()->create(['deactivated_at' => now()]);

    $this->actingAs($this->admin)->post(route('admin.staff.password-link', $leaver))->assertConflict();

    Mail::assertNotSent(StaffInvitation::class);
});

test('a leaver\'s timesheet is listed for the month they left but not afterwards', function () {
    $this->travelTo('2026-12-15 10:00:00');
    $leaver = User::factory()->create(['deactivated_at' => '2026-11-20 16:00:00']);

    $this->actingAs($this->admin)->get(route('admin.attendance.index', ['month' => '2026-11']))
        ->assertInertia(fn (Assert $page) => $page->where('staff', fn ($staff) => collect($staff)->pluck('id')->contains($leaver->id)));
    $this->actingAs($this->admin)->get(route('admin.attendance.index', ['month' => '2026-12']))
        ->assertInertia(fn (Assert $page) => $page->where('staff', fn ($staff) => ! collect($staff)->pluck('id')->contains($leaver->id)));
});
