<?php

use App\Mail\DonationReceived;
use App\Models\Donation;

test('the notification tells CHICD who gave, how much and to what, and replies to the donor', function () {
    $donation = Donation::factory()->forSurl()->successful()->create([
        'donor_name' => 'Ada Okon',
        'donor_email' => 'ada@example.com',
        'donor_phone' => '08030000000',
        'amount' => 2500000,
    ]);

    $mail = new DonationReceived($donation);

    $mail->assertHasSubject('New donation: ₦25,000.00 to Show Up Ready to Learn (SURL)');
    $mail->assertHasReplyTo('ada@example.com', 'Ada Okon');
    $mail->assertSeeInHtml('Ada Okon');
    $mail->assertSeeInHtml('08030000000');
    $mail->assertSeeInHtml($donation->reference);
});
