<?php

use App\Enums\UserRole;
use App\Mail\StaffInvitation;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create(['name' => 'Zara Admin']);
});

test('staff cannot see staff accounts', function () {
    $this->actingAs(User::factory()->create())->get(route('admin.staff.index'))->assertForbidden();
});

test('admins see every account, admins first', function () {
    $staffMember = User::factory()->create(['name' => 'Ada Okon']);

    $this->actingAs($this->admin)->get(route('admin.staff.index'))->assertInertia(fn (Assert $page) => $page
        ->component('admin/staff/Index')
        ->where('staff.0.id', $this->admin->id)
        ->where('staff.0.is_current_user', true)
        ->where('staff.1.id', $staffMember->id)
        ->where('staff.1.role', 'staff')
    );
});

test('an admin adds a staff member, who sets their own password from the emailed link and signs in', function () {
    Mail::fake();

    $this->actingAs($this->admin)->post(route('admin.staff.store'), [
        'name' => 'Ada Okon',
        'email' => 'Ada@Example.com',
        'role' => 'staff',
    ])->assertSessionHasNoErrors()->assertRedirect(route('admin.staff.index'));

    $staffMember = User::query()->where('email', 'ada@example.com')->sole();
    expect($staffMember->name)->toBe('Ada Okon')
        ->and($staffMember->role)->toBe(UserRole::Staff)
        ->and($staffMember->hasVerifiedEmail())->toBeTrue();

    $setPasswordUrl = null;
    Mail::assertSent(StaffInvitation::class, function (StaffInvitation $mail) use (&$setPasswordUrl) {
        $setPasswordUrl = $mail->setPasswordUrl;

        return $mail->hasTo('ada@example.com');
    });

    auth()->logout();
    $token = basename(parse_url($setPasswordUrl, PHP_URL_PATH));
    $this->post(route('password.update'), [
        'token' => $token,
        'email' => 'ada@example.com',
        'password' => 'a-long-new-password',
        'password_confirmation' => 'a-long-new-password',
    ])->assertSessionHasNoErrors();

    expect(Hash::check('a-long-new-password', $staffMember->refresh()->password))->toBeTrue();
});

test('a staff member cannot be added twice with the same email address', function () {
    Mail::fake();
    User::factory()->create(['email' => 'ada@example.com']);

    $this->actingAs($this->admin)->post(route('admin.staff.store'), [
        'name' => 'Ada Okon',
        'email' => 'ada@example.com',
        'role' => 'staff',
    ])->assertSessionHasErrors(['email' => 'Someone already has an account with this email address.']);

    Mail::assertNothingSent();
});

test('a new account needs a name, email address and permissions', function () {
    $this->actingAs($this->admin)->post(route('admin.staff.store'), [])
        ->assertSessionHasErrors(['name', 'email', 'role']);
});

test('an admin changes another staff member\'s permissions', function () {
    $staffMember = User::factory()->create();

    $this->actingAs($this->admin)->patch(route('admin.staff.update', $staffMember), ['role' => 'admin'])
        ->assertSessionHasNoErrors();

    expect($staffMember->refresh()->role)->toBe(UserRole::Admin);
});

test('an admin cannot change their own permissions', function () {
    $this->actingAs($this->admin)->patch(route('admin.staff.update', $this->admin), ['role' => 'staff'])
        ->assertSessionHasErrors(['role' => 'You cannot change your own permissions. Ask another admin to do it.']);

    expect($this->admin->refresh()->role)->toBe(UserRole::Admin);
});

test('staff cannot give themselves admin permissions', function () {
    $staffMember = User::factory()->create();

    $this->actingAs($staffMember)->patch(route('admin.staff.update', $staffMember), ['role' => 'admin'])
        ->assertForbidden();

    expect($staffMember->refresh()->role)->toBe(UserRole::Staff);
});

test('an admin emails a staff member a new password link', function () {
    Mail::fake();
    $staffMember = User::factory()->create(['email' => 'ada@example.com']);

    $this->actingAs($this->admin)->post(route('admin.staff.password-link', $staffMember))->assertRedirect();

    Mail::assertSent(StaffInvitation::class, fn (StaffInvitation $mail) => $mail->hasTo('ada@example.com'));
});
