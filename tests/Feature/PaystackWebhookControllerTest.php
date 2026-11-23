<?php

use App\Enums\DonationFrequency;
use App\Enums\DonationStatus;
use App\Mail\DonationReceipt;
use App\Mail\DonationReceived;
use App\Models\Donation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;

beforeEach(function () {
    config([
        'services.paystack.secret_key' => 'sk_test_chicd',
        'chicd.donations_email' => 'donations@chicd.test',
    ]);
});

/**
 * Send a webhook event signed the way Paystack signs it.
 *
 * @param  array<string, mixed>  $data
 */
function sendPaystackEvent(string $event, array $data, ?string $secret = 'sk_test_chicd'): TestResponse
{
    $body = json_encode(['event' => $event, 'data' => $data], JSON_THROW_ON_ERROR);

    return test()->call('POST', route('webhooks.paystack'), [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_PAYSTACK_SIGNATURE' => $secret === null ? '' : hash_hmac('sha512', $body, $secret),
    ], $body);
}

/**
 * A charge.success "data" object.
 *
 * @return array<string, mixed>
 */
function successfulCharge(string $reference, int $amount, array $overrides = []): array
{
    return [
        'id' => 4099260516,
        'status' => 'success',
        'reference' => $reference,
        'amount' => $amount,
        'currency' => 'NGN',
        'channel' => 'card',
        'fees' => 85000,
        'paid_at' => '2026-11-23T09:30:00.000Z',
        'customer' => ['customer_code' => 'CUS_donor001', 'email' => 'ada@example.com'],
        'plan' => [],
        ...$overrides,
    ];
}

test('an event without a valid Paystack signature is refused', function () {
    Mail::fake();
    $donation = Donation::factory()->create();

    sendPaystackEvent('charge.success', successfulCharge($donation->reference, $donation->amount), secret: 'sk_wrong')
        ->assertUnauthorized();

    expect($donation->refresh()->status)->toBe(DonationStatus::Pending);
    Mail::assertNothingQueued();
});

test('a successful charge confirms the donation and sends the receipt and notification only once', function () {
    Mail::fake();
    $donation = Donation::factory()->create(['amount' => 2500000, 'donor_email' => 'ada@example.com']);

    sendPaystackEvent('charge.success', successfulCharge($donation->reference, 2500000))->assertOk();
    sendPaystackEvent('charge.success', successfulCharge($donation->reference, 2500000))->assertOk();

    expect($donation->refresh()->status)->toBe(DonationStatus::Successful);
    Mail::assertQueuedCount(2);
    Mail::assertQueued(DonationReceipt::class, fn (DonationReceipt $mail) => $mail->hasTo('ada@example.com'));
    Mail::assertQueued(DonationReceived::class, fn (DonationReceived $mail) => $mail->hasTo('donations@chicd.test'));
});

test('a later payment of a recurring gift is recorded against the same fund and receipted', function () {
    Mail::fake();
    $firstPayment = Donation::factory()->forSurl()->monthly()->successful()->create([
        'amount' => 1000000,
        'donor_email' => 'ada@example.com',
        'paystack_customer_code' => 'CUS_donor001',
        'paystack_plan_code' => 'PLN_monthly10k',
    ]);

    sendPaystackEvent('charge.success', successfulCharge('T685312322670591', 1000000, [
        'plan' => ['plan_code' => 'PLN_monthly10k', 'interval' => 'monthly'],
    ]))->assertOk();

    $renewal = Donation::query()->where('reference', 'T685312322670591')->sole();
    expect($renewal->parent_id)->toBe($firstPayment->id)
        ->and($renewal->status)->toBe(DonationStatus::Successful)
        ->and($renewal->fund_label)->toBe('Show Up Ready to Learn (SURL)')
        ->and($renewal->frequency)->toBe(DonationFrequency::Monthly)
        ->and($renewal->amount)->toBe(1000000);
    Mail::assertQueued(DonationReceipt::class, fn (DonationReceipt $mail) => $mail->hasTo('ada@example.com') && $mail->donation->is($renewal));
});

test('a charge that is not for a donation is acknowledged and ignored', function () {
    Mail::fake();

    sendPaystackEvent('charge.success', successfulCharge('INVOICE-123', 500000))->assertOk();

    $this->assertDatabaseEmpty('donations');
    Mail::assertNothingQueued();
});

test('a new subscription is linked to the recurring gift it pays for', function () {
    $gift = Donation::factory()->monthly()->create([
        'donor_email' => 'ada@example.com',
        'paystack_plan_code' => 'PLN_monthly10k',
    ]);

    sendPaystackEvent('subscription.create', [
        'subscription_code' => 'SUB_vsyqdmlzble3uii',
        'plan' => ['plan_code' => 'PLN_monthly10k'],
        'customer' => ['customer_code' => 'CUS_donor001', 'email' => 'ada@example.com'],
    ])->assertOk();

    $gift->refresh();
    expect($gift->paystack_subscription_code)->toBe('SUB_vsyqdmlzble3uii')
        ->and($gift->paystack_customer_code)->toBe('CUS_donor001');
});
