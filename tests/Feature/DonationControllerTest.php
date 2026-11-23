<?php

use App\Enums\DonationFrequency;
use App\Enums\DonationStatus;
use App\Mail\DonationReceipt;
use App\Mail\DonationReceived;
use App\Models\Donation;
use App\Models\Programme;
use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    config([
        'services.paystack.secret_key' => 'sk_test_chicd',
        'chicd.donations_email' => 'donations@chicd.test',
        // Server-side rendering would call the Vite dev server through the faked HTTP client.
        'inertia.ssr.enabled' => false,
    ]);
});

/**
 * A Paystack transaction "data" object for a donation, as returned when the
 * transaction is verified.
 *
 * @return array<string, mixed>
 */
function verifiedTransaction(Donation $donation, array $overrides = []): array
{
    return [
        'id' => 4099260516,
        'status' => 'success',
        'reference' => $donation->reference,
        'amount' => $donation->amount,
        'currency' => 'NGN',
        'channel' => 'card',
        'fees' => 85000,
        'paid_at' => '2026-11-23T09:30:00.000Z',
        'customer' => ['customer_code' => 'CUS_donor001', 'email' => $donation->donor_email],
        'plan' => null,
        ...$overrides,
    ];
}

/**
 * The donation form, filled in for a one-time gift to CHICD.
 *
 * @return array<string, mixed>
 */
function donationForm(array $overrides = []): array
{
    return [
        'fund' => 'general',
        'frequency' => 'one_time',
        'amount' => 25000,
        'name' => 'Ada Okon',
        'email' => 'ada@example.com',
        'phone' => '08030000000',
        ...$overrides,
    ];
}

test('the donate page offers CHICD, the flagship programmes and published added programmes', function () {
    Programme::factory()->create(['name' => 'Community Health Outreach', 'slug' => 'community-health-outreach']);
    Programme::factory()->unpublished()->create(['name' => 'Draft Programme', 'slug' => 'draft-programme']);

    $this->get(route('donate', ['fund' => 'community-health-outreach']))->assertInertia(fn (Assert $page) => $page
        ->component('site/Donate')
        ->where('funds', [
            ['value' => 'general', 'label' => 'Donate to CHICD (where it is needed most)'],
            ['value' => 'surl', 'label' => 'Show Up Ready to Learn (SURL)'],
            ['value' => 'safe-housing', 'label' => 'Safe Housing Programme'],
            ['value' => 'community-health-outreach', 'label' => 'Community Health Outreach'],
        ])
        ->where('selectedFund', 'community-health-outreach')
        ->where('frequencies.0.value', 'one_time')
    );
});

test('the donate page gives to CHICD generally when the requested fund is unknown', function () {
    $this->get(route('donate', ['fund' => 'no-such-programme']))
        ->assertInertia(fn (Assert $page) => $page->where('selectedFund', 'general'));
});

test('a one-time donation is recorded as pending and the Paystack popup is opened for it', function () {
    Http::preventStrayRequests();
    Http::fake([
        'api.paystack.co/transaction/initialize' => Http::response(['status' => true, 'data' => ['authorization_url' => 'https://checkout.paystack.com/abc123', 'access_code' => 'abc123', 'reference' => 'ignored']]),
    ]);

    $response = $this->from(route('donate', ['fund' => 'surl']))->post(route('donations.store'), donationForm(['fund' => 'surl']));

    $donation = Donation::sole();
    $response->assertRedirect(route('donate', ['fund' => 'surl']))
        ->assertInertiaFlash('paystack', [
            'reference' => $donation->reference,
            'accessCode' => 'abc123',
            'checkoutUrl' => 'https://checkout.paystack.com/abc123',
        ]);
    expect($donation->status)->toBe(DonationStatus::Pending)
        ->and($donation->fund)->toBe('surl')
        ->and($donation->fund_label)->toBe('Show Up Ready to Learn (SURL)')
        ->and($donation->frequency)->toBe(DonationFrequency::OneTime)
        ->and($donation->amount)->toBe(2500000)
        ->and($donation->donor_email)->toBe('ada@example.com')
        ->and($donation->paystack_plan_code)->toBeNull();

    Http::assertSent(fn (Request $request) => $request->url() === 'https://api.paystack.co/transaction/initialize'
        && $request->hasHeader('Authorization', 'Bearer sk_test_chicd')
        && $request['amount'] === 2500000
        && $request['currency'] === 'NGN'
        && $request['email'] === 'ada@example.com'
        && $request['reference'] === $donation->reference
        && $request['callback_url'] === route('donations.callback')
        && ! isset($request['plan']));
});

test('a monthly donation subscribes the donor to a Paystack plan for the amount, created once', function () {
    Http::preventStrayRequests();
    Http::fake([
        'api.paystack.co/plan' => Http::response(['status' => true, 'data' => ['plan_code' => 'PLN_monthly25k']]),
        'api.paystack.co/transaction/initialize' => Http::response(['status' => true, 'data' => ['authorization_url' => 'https://checkout.paystack.com/abc123', 'access_code' => 'abc123', 'reference' => 'ignored']]),
    ]);

    $this->post(route('donations.store'), donationForm(['frequency' => 'monthly']))->assertInertiaFlash('paystack.accessCode', 'abc123');
    $this->post(route('donations.store'), donationForm(['frequency' => 'monthly', 'email' => 'second@example.com']))->assertRedirect();

    expect(Donation::pluck('paystack_plan_code')->all())->toBe(['PLN_monthly25k', 'PLN_monthly25k']);
    Http::assertSentCount(3);
    Http::assertSent(fn (Request $request) => $request->url() === 'https://api.paystack.co/plan'
        && $request['interval'] === 'monthly'
        && $request['amount'] === 2500000);
    Http::assertSent(fn (Request $request) => $request->url() === 'https://api.paystack.co/transaction/initialize'
        && $request['plan'] === 'PLN_monthly25k');
});

test('a donation needs a fund, frequency, amount, name and email address', function () {
    Http::preventStrayRequests();

    $this->post(route('donations.store'), [])->assertSessionHasErrors([
        'fund',
        'frequency',
        'amount' => 'Please enter the amount you would like to give.',
        'name',
        'email' => 'Please give an email address so we can send your receipt.',
    ]);

    $this->assertDatabaseEmpty('donations');
});

test('a donation cannot be made to a programme that is not published', function () {
    Http::preventStrayRequests();
    Programme::factory()->unpublished()->create(['slug' => 'draft-programme']);

    $this->post(route('donations.store'), donationForm(['fund' => 'draft-programme']))
        ->assertSessionHasErrors(['fund' => 'Please choose what you would like to support.']);

    $this->assertDatabaseEmpty('donations');
});

test('a donation below the online minimum is rejected', function () {
    Http::preventStrayRequests();

    $this->post(route('donations.store'), donationForm(['amount' => 99]))
        ->assertSessionHasErrors(['amount' => 'The smallest gift we can take online is ₦100.']);
});

test('a donation that fills the hidden spam trap is rejected', function () {
    Http::preventStrayRequests();

    $this->post(route('donations.store'), donationForm(['website' => 'https://spam.example.com']))
        ->assertSessionHasErrors('website');

    $this->assertDatabaseEmpty('donations');
});

test('the donor is told to try again and nothing is recorded when Paystack cannot be reached', function () {
    Http::preventStrayRequests();
    Http::fake(['api.paystack.co/transaction/initialize' => Http::failedConnection()]);

    $this->post(route('donations.store'), donationForm())->assertSessionHasErrors([
        'payment' => 'We could not reach our payment provider. Your card has not been charged. Please try again in a few minutes.',
    ]);

    $this->assertDatabaseEmpty('donations');
});

test('nothing is recorded when Paystack does not return an access code', function () {
    Http::preventStrayRequests();
    Http::fake([
        'api.paystack.co/transaction/initialize' => Http::response(['status' => true, 'data' => ['authorization_url' => 'https://checkout.paystack.com/abc123']]),
    ]);

    $this->post(route('donations.store'), donationForm())
        ->assertSessionHasErrors('payment')
        ->assertInertiaFlashMissing('paystack');

    $this->assertDatabaseEmpty('donations');
});

test('returning from Paystack confirms the payment, sends the receipt and notifies CHICD', function () {
    Mail::fake();
    Http::preventStrayRequests();
    $donation = Donation::factory()->forSurl()->create(['amount' => 2500000, 'donor_name' => 'Ada Okon', 'donor_email' => 'ada@example.com']);
    Http::fake([
        "api.paystack.co/transaction/verify/{$donation->reference}" => Http::response(['status' => true, 'data' => verifiedTransaction($donation)]),
    ]);

    $this->get(route('donations.callback', ['trxref' => $donation->reference, 'reference' => $donation->reference]))
        ->assertInertia(fn (Assert $page) => $page
            ->component('site/DonationStatus')
            ->where('donation.status', 'successful')
            ->where('donation.firstName', 'Ada')
            ->where('donation.amount', '₦25,000.00')
            ->where('donation.fund', 'Show Up Ready to Learn (SURL)')
        );

    $donation->refresh();
    expect($donation->status)->toBe(DonationStatus::Successful)
        ->and($donation->paid_at->toIso8601String())->toBe('2026-11-23T09:30:00+00:00')
        ->and($donation->fees)->toBe(85000)
        ->and($donation->channel)->toBe('card')
        ->and($donation->paystack_transaction_id)->toBe(4099260516)
        ->and($donation->paystack_customer_code)->toBe('CUS_donor001');

    Mail::assertQueued(DonationReceipt::class, fn (DonationReceipt $mail) => $mail->hasTo('ada@example.com') && $mail->donation->is($donation));
    Mail::assertQueued(DonationReceived::class, fn (DonationReceived $mail) => $mail->hasTo('donations@chicd.test') && $mail->donation->is($donation));
});

test('returning again after the payment is confirmed sends no second receipt', function () {
    Mail::fake();
    Http::preventStrayRequests();
    $donation = Donation::factory()->successful()->create();

    $this->get(route('donations.callback', ['reference' => $donation->reference]))
        ->assertInertia(fn (Assert $page) => $page->where('donation.status', 'successful'));

    Mail::assertNothingQueued();
});

test('a failed payment is recorded and the donor is not sent a receipt', function () {
    Mail::fake();
    Http::preventStrayRequests();
    $donation = Donation::factory()->create();
    Http::fake([
        "api.paystack.co/transaction/verify/{$donation->reference}" => Http::response(['status' => true, 'data' => verifiedTransaction($donation, ['status' => 'failed'])]),
    ]);

    $this->get(route('donations.callback', ['reference' => $donation->reference]))
        ->assertInertia(fn (Assert $page) => $page->where('donation.status', 'failed'));

    expect($donation->refresh()->status)->toBe(DonationStatus::Failed);
    Mail::assertNothingQueued();
});

test('a payment for a different amount than the donor chose is not confirmed', function () {
    Mail::fake();
    Http::preventStrayRequests();
    $donation = Donation::factory()->create(['amount' => 2500000]);
    Http::fake([
        "api.paystack.co/transaction/verify/{$donation->reference}" => Http::response(['status' => true, 'data' => verifiedTransaction($donation, ['amount' => 10000])]),
    ]);

    $this->get(route('donations.callback', ['reference' => $donation->reference]))
        ->assertInertia(fn (Assert $page) => $page->where('donation.status', 'pending'));

    Mail::assertNothingQueued();
});

test('the donor sees the payment as still being confirmed when Paystack cannot be reached', function () {
    Mail::fake();
    Http::preventStrayRequests();
    $donation = Donation::factory()->create();
    Http::fake(["api.paystack.co/transaction/verify/{$donation->reference}" => Http::failedConnection()]);

    $this->get(route('donations.callback', ['reference' => $donation->reference]))
        ->assertInertia(fn (Assert $page) => $page->where('donation.status', 'pending'));

    Mail::assertNothingQueued();
});

test('returning with an unknown reference is not found', function () {
    Http::preventStrayRequests();

    $this->get(route('donations.callback', ['reference' => 'CHICD-UNKNOWN']))->assertNotFound();
});

test('acceptance: a donation to a named programme is receipted, notified, listed and exported with that programme', function () {
    Mail::fake();
    Http::preventStrayRequests();
    $this->travelTo('2026-11-23 10:00:00');
    Programme::factory()->create(['name' => 'Community Health Outreach', 'slug' => 'community-health-outreach']);
    Http::fake([
        'api.paystack.co/transaction/initialize' => Http::response(['status' => true, 'data' => ['authorization_url' => 'https://checkout.paystack.com/abc123', 'access_code' => 'abc123', 'reference' => 'ignored']]),
        'api.paystack.co/transaction/verify/*' => fn (Request $request) => Http::response(['status' => true, 'data' => verifiedTransaction(Donation::sole())]),
    ]);

    $this->post(route('donations.store'), donationForm(['fund' => 'community-health-outreach', 'name' => 'Tester One', 'email' => 'tester@example.com']))
        ->assertInertiaFlash('paystack.accessCode', 'abc123');
    $donation = Donation::sole();
    $this->get(route('donations.callback', ['reference' => $donation->reference]))
        ->assertInertia(fn (Assert $page) => $page->where('donation.status', 'successful'));

    Mail::assertQueued(DonationReceipt::class, fn (DonationReceipt $mail) => $mail->hasTo('tester@example.com'));
    Mail::assertQueued(DonationReceived::class, fn (DonationReceived $mail) => $mail->hasTo('donations@chicd.test')
        && $mail->donation->fund_label === 'Community Health Outreach');

    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->get(route('admin.donations.index', ['month' => '2026-11']))->assertInertia(fn (Assert $page) => $page
        ->where('donations.0.reference', $donation->reference)
        ->where('donations.0.fund', 'Community Health Outreach')
    );
    $csv = $this->actingAs($admin)->get(route('admin.donations.export', ['month' => '2026-11']))->streamedContent();
    expect($csv)->toContain($donation->reference.',"2026-11-23 10:30","Tester One",tester@example.com,08030000000,"Community Health Outreach",community-health-outreach');
});
