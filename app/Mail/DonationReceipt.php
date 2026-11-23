<?php

namespace App\Mail;

use App\Models\Donation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The donor's acknowledgement and receipt for a confirmed donation.
 */
class DonationReceipt extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public Donation $donation) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Thank you for your gift to '.config('chicd.short_name').' (receipt '.$this->donation->reference.')',
            replyTo: [new Address(config('chicd.donations_email'), config('chicd.short_name'))],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.donation-receipt',
            with: [
                'paidOn' => $this->donation->paid_at?->setTimezone(config('chicd.timezone'))->format('j F Y, H:i'),
            ],
        );
    }
}
