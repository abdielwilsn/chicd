<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Invites a staff member to the staff portal with a link to set their
 * password. Sent straight away rather than queued, so the password link is
 * never stored in the jobs table.
 */
class StaffInvitation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public User $user, public string $setPasswordUrl) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your '.config('chicd.short_name').' staff portal account',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.staff-invitation',
            with: [
                'expiresInMinutes' => config('auth.passwords.'.config('fortify.passwords').'.expire'),
            ],
        );
    }
}
