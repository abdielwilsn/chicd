<?php

use App\Mail\EnquiryReceived;
use App\Models\Enquiry;

test('the notification includes the enquiry details and replies to the sender', function () {
    $enquiry = Enquiry::factory()->create([
        'name' => 'Ada Okon',
        'email' => 'ada@example.com',
        'subject' => 'Research collaboration',
        'message' => 'We would like to partner on a needs assessment.',
    ]);

    $mail = new EnquiryReceived($enquiry);

    $mail->assertHasSubject('General enquiry from the CHICD website');
    $mail->assertHasReplyTo('ada@example.com', 'Ada Okon');
    $mail->assertSeeInHtml('Research collaboration');
    $mail->assertSeeInHtml('We would like to partner on a needs assessment.');
    $mail->assertDontSeeInHtml('sensitive personal circumstances');
});

test('a safe housing notification carries a confidentiality warning', function () {
    $enquiry = Enquiry::factory()->safeHousing()->create([
        'email' => null,
        'phone' => '08030000000',
    ]);

    $mail = new EnquiryReceived($enquiry);

    $mail->assertHasSubject('Confidential Safe Housing enquiry from the CHICD website');
    $mail->assertSeeInHtml('sensitive personal circumstances');
    $mail->assertSeeInHtml('08030000000');
});
