<?php

use App\Mail\StaffInvitation;
use App\Models\User;

test('the invitation gives the username, the password link and how long it lasts', function () {
    config(['auth.passwords.users.expire' => 60]);
    $user = User::factory()->create(['name' => 'Ada Okon', 'email' => 'ada@example.com']);

    $mail = new StaffInvitation($user, 'https://chicd.test/reset-password/abc123?email=ada%40example.com');

    $mail->assertHasSubject('Your CHICD staff portal account');
    $mail->assertSeeInHtml('Welcome, Ada');
    $mail->assertSeeInHtml('ada@example.com');
    $mail->assertSeeInHtml('https://chicd.test/reset-password/abc123?email=ada%40example.com', escape: false);
    $mail->assertSeeInHtml('This link works for 60 minutes.');
});
