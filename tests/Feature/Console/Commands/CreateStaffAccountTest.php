<?php

use App\Enums\UserRole;
use App\Mail\StaffInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('the command creates an admin account and emails a password link', function () {
    Mail::fake();

    $this->artisan('staff:create', ['email' => 'director@chicd.org', 'name' => 'Grace Etim', '--admin' => true])
        ->expectsOutputToContain('Admin account created for Grace Etim')
        ->assertSuccessful();

    expect(User::query()->where('email', 'director@chicd.org')->sole()->role)->toBe(UserRole::Admin);
    Mail::assertSent(StaffInvitation::class, fn (StaffInvitation $mail) => $mail->hasTo('director@chicd.org'));
});

test('the command creates a staff account by default', function () {
    Mail::fake();

    $this->artisan('staff:create', ['email' => 'ada@chicd.org', 'name' => 'Ada Okon'])->assertSuccessful();

    expect(User::query()->where('email', 'ada@chicd.org')->sole()->role)->toBe(UserRole::Staff);
});

test('the command refuses an email address that already has an account', function () {
    Mail::fake();
    User::factory()->create(['email' => 'ada@chicd.org']);

    $this->artisan('staff:create', ['email' => 'ada@chicd.org', 'name' => 'Ada Okon'])
        ->expectsOutputToContain('The email has already been taken.')
        ->assertFailed();

    Mail::assertNothingSent();
});
