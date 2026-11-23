<?php

use App\Models\Office;
use App\Models\User;

test('seeding a live server creates the office but no test accounts', function () {
    app()->detectEnvironment(fn () => 'production');

    $this->artisan('db:seed', ['--force' => true])->assertSuccessful();

    expect(User::count())->toBe(0)
        ->and(Office::count())->toBe(1);
});

test('seeding a local machine creates the test accounts', function () {
    app()->detectEnvironment(fn () => 'local');

    $this->seed();

    expect(User::query()->where('email', 'admin@example.com')->exists())->toBeTrue();
});
