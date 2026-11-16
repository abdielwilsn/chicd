<?php

use App\Models\NewsletterSubscriber;

test('a visitor can subscribe to updates', function () {
    $response = $this->from(route('home'))->post(route('newsletter.store'), [
        'email' => 'Ada@Example.com',
    ]);

    $response->assertSessionHasNoErrors()->assertRedirect(route('home'));
    $this->assertDatabaseHas('newsletter_subscribers', ['email' => 'ada@example.com']);
});

test('subscribing again does not create a duplicate or reveal the existing subscription', function () {
    NewsletterSubscriber::factory()->create(['email' => 'ada@example.com']);

    $response = $this->post(route('newsletter.store'), ['email' => 'ADA@example.com']);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseCount('newsletter_subscribers', 1);
});

test('a previously unsubscribed address is subscribed again', function () {
    $subscriber = NewsletterSubscriber::factory()->create([
        'email' => 'ada@example.com',
        'unsubscribed_at' => now()->subMonth(),
    ]);

    $this->post(route('newsletter.store'), ['email' => 'ada@example.com']);

    expect($subscriber->fresh()->unsubscribed_at)->toBeNull();
});

test('an invalid email address is rejected', function () {
    $response = $this->post(route('newsletter.store'), ['email' => 'not-an-email']);

    $response->assertSessionHasErrors('email');
    $this->assertDatabaseEmpty('newsletter_subscribers');
});
