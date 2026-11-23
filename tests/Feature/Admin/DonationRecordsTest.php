<?php

use App\Models\Donation;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

test('staff cannot see donation records', function () {
    $this->actingAs(User::factory()->create())->get(route('admin.donations.index'))->assertForbidden();
});

test('admins see the month\'s paid donations with totals by fund, and unpaid ones separately', function () {
    $this->travelTo('2026-11-23 10:00:00');
    $surl = Donation::factory()->forSurl()->successful()->create(['amount' => 2500000, 'fees' => 50000, 'paid_at' => '2026-11-10 09:00:00']);
    $general = Donation::factory()->successful()->create(['amount' => 1000000, 'fees' => 25000, 'paid_at' => '2026-11-05 09:00:00']);
    Donation::factory()->successful()->create(['paid_at' => '2026-10-31 22:00:00']);
    $unpaid = Donation::factory()->create(['created_at' => '2026-11-12 09:00:00']);

    $this->actingAs($this->admin)->get(route('admin.donations.index', ['month' => '2026-11']))->assertInertia(fn (Assert $page) => $page
        ->component('admin/donations/Index')
        ->where('monthLabel', 'November 2026')
        ->where('totals', ['count' => 2, 'amount' => '₦35,000.00', 'fees' => '₦750.00', 'net' => '₦34,250.00'])
        ->where('funds', [
            ['fund' => 'Show Up Ready to Learn (SURL)', 'count' => 1, 'amount' => '₦25,000.00'],
            ['fund' => 'Donate to CHICD (where it is needed most)', 'count' => 1, 'amount' => '₦10,000.00'],
        ])
        ->where('donations.0.reference', $surl->reference)
        ->where('donations.1.reference', $general->reference)
        ->has('donations', 2)
        ->where('incomplete.0.reference', $unpaid->reference)
        ->has('incomplete', 1)
    );
});

test('a donation paid just after midnight Lagos time on the first of the month belongs to that month', function () {
    $this->travelTo('2026-11-23 10:00:00');
    $donation = Donation::factory()->successful()->create(['paid_at' => '2026-10-31 23:30:00']);

    $this->actingAs($this->admin)->get(route('admin.donations.index', ['month' => '2026-11']))
        ->assertInertia(fn (Assert $page) => $page->where('donations.0.reference', $donation->reference));
});

test('the export lists the month\'s paid donations for reconciliation', function () {
    $this->travelTo('2026-11-23 10:00:00');
    $donation = Donation::factory()->forSurl()->successful()->create([
        'reference' => 'CHICD-01KAEXPORT',
        'donor_name' => 'Ada Okon',
        'donor_email' => 'ada@example.com',
        'amount' => 2500000,
        'fees' => 47500,
        'paid_at' => '2026-11-10 09:00:00',
        'paystack_transaction_id' => 4099260516,
    ]);
    $unpaid = Donation::factory()->create(['created_at' => '2026-11-12 09:00:00']);

    $response = $this->actingAs($this->admin)->get(route('admin.donations.export', ['month' => '2026-11']));

    $response->assertOk()->assertDownload('chicd-donations-2026-11.csv');
    $csv = $response->streamedContent();
    expect($csv)->toContain('Reference,"Date paid",Donor,Email,Phone,Supporting,"Fund code",Frequency')
        ->and($csv)->toContain('CHICD-01KAEXPORT,"2026-11-10 10:00","Ada Okon",ada@example.com,,"Show Up Ready to Learn (SURL)",surl,One-time,,NGN,25000.00,475.00,24525.00,card,Successful,4099260516,')
        ->and($csv)->not->toContain($unpaid->reference);
});
