<?php

use App\Enums\EnquiryType;
use App\Mail\EnquiryReceived;
use App\Models\Enquiry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    config(['chicd.enquiries_email' => 'enquiries@chicd.test']);
});

test('an enquiry is stored and the enquiries inbox is notified', function () {
    Mail::fake();

    $response = $this->from(route('contact'))->post(route('enquiries.store'), [
        'type' => 'general',
        'name' => 'Ada Okon',
        'email' => 'ada@example.com',
        'organisation' => 'Uyo Community Health Network',
        'subject' => 'Programmes',
        'message' => 'I would like to learn more about SURL.',
    ]);

    $response->assertSessionHasNoErrors()->assertRedirect(route('contact'));

    $enquiry = Enquiry::sole();
    expect($enquiry->type)->toBe(EnquiryType::General)
        ->and($enquiry->name)->toBe('Ada Okon')
        ->and($enquiry->email)->toBe('ada@example.com')
        ->and($enquiry->organisation)->toBe('Uyo Community Health Network')
        ->and($enquiry->subject)->toBe('Programmes')
        ->and($enquiry->message)->toBe('I would like to learn more about SURL.');

    Mail::assertQueued(EnquiryReceived::class, fn (EnquiryReceived $mail) => $mail->hasTo('enquiries@chicd.test') && $mail->enquiry->is($enquiry));
});

test('personal details and the message are encrypted at rest', function () {
    Mail::fake();

    $this->post(route('enquiries.store'), [
        'type' => 'safe_housing',
        'name' => 'Chi',
        'phone' => '08030000000',
        'message' => 'I need somewhere safe to stay.',
    ]);

    $stored = DB::table('enquiries')->sole();
    expect($stored->name)->not->toBe('Chi')
        ->and($stored->phone)->not->toBe('08030000000')
        ->and($stored->message)->not->toContain('somewhere safe');
});

test('a safe housing enquiry may be sent with a phone number instead of an email address', function () {
    Mail::fake();

    $response = $this->post(route('enquiries.store'), [
        'type' => 'safe_housing',
        'name' => 'Chi',
        'phone' => '08030000000',
        'message' => 'I need somewhere safe to stay.',
    ]);

    $response->assertSessionHasNoErrors();
    expect(Enquiry::sole()->email)->toBeNull();
});

test('a safe housing enquiry needs an email address or a phone number', function () {
    Mail::fake();

    $response = $this->post(route('enquiries.store'), [
        'type' => 'safe_housing',
        'name' => 'Chi',
        'message' => 'I need somewhere safe to stay.',
    ]);

    $response->assertSessionHasErrors([
        'email' => 'Please give us an email address or a phone number so we can reply.',
    ]);
    $this->assertDatabaseEmpty('enquiries');
    Mail::assertNothingQueued();
});

test('other enquiries require an email address even when a phone number is given', function () {
    Mail::fake();

    $response = $this->post(route('enquiries.store'), [
        'type' => 'volunteer',
        'name' => 'Ada Okon',
        'phone' => '08030000000',
        'message' => 'I would like to volunteer.',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertDatabaseEmpty('enquiries');
});

test('an enquiry without required fields is rejected', function () {
    Mail::fake();

    $response = $this->post(route('enquiries.store'), []);

    $response->assertSessionHasErrors(['type', 'name', 'email', 'message']);
    $this->assertDatabaseEmpty('enquiries');
    Mail::assertNothingQueued();
});

test('an enquiry with an unknown type is rejected', function () {
    Mail::fake();

    $response = $this->post(route('enquiries.store'), [
        'type' => 'sponsorship',
        'name' => 'Ada Okon',
        'email' => 'ada@example.com',
        'message' => 'Hello.',
    ]);

    $response->assertSessionHasErrors('type');
    $this->assertDatabaseEmpty('enquiries');
});

test('an enquiry that fills the hidden spam trap is rejected', function () {
    Mail::fake();

    $response = $this->post(route('enquiries.store'), [
        'type' => 'general',
        'name' => 'Bot',
        'email' => 'bot@example.com',
        'message' => 'Buy now.',
        'website' => 'https://spam.example.com',
    ]);

    $response->assertSessionHasErrors('website');
    $this->assertDatabaseEmpty('enquiries');
    Mail::assertNothingQueued();
});
