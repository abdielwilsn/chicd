<?php

use App\Mail\DonationReceipt;
use App\Models\Donation;

test('the receipt thanks the donor and shows what they gave, to what and when', function () {
    config(['chicd.donations_email' => 'donations@chicd.test']);
    $donation = Donation::factory()->forSurl()->successful()->create([
        'reference' => 'CHICD-01KARECEIPT',
        'donor_name' => 'Ada Okon',
        'amount' => 2500000,
        'paid_at' => '2026-11-23 09:30:00',
    ]);

    $mail = new DonationReceipt($donation);

    $mail->assertHasSubject('Thank you for your gift to CHICD (receipt CHICD-01KARECEIPT)');
    $mail->assertHasReplyTo('donations@chicd.test');
    $mail->assertSeeInHtml('Thank you, Ada');
    $mail->assertSeeInHtml('CHICD-01KARECEIPT');
    $mail->assertSeeInHtml('₦25,000.00');
    $mail->assertSeeInHtml('Show Up Ready to Learn (SURL)');
    $mail->assertSeeInHtml('23 November 2026, 10:30');
    $mail->assertDontSeeInHtml('until you cancel');
});

test('the first receipt for a monthly gift explains the repeat payments', function () {
    $donation = Donation::factory()->monthly()->successful()->create(['amount' => 1000000]);

    (new DonationReceipt($donation))->assertSeeInHtml('Paystack will charge ₦10,000.00 on the same schedule until you cancel');
});
