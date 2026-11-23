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
 * Tells CHICD that a donation has been confirmed by Paystack.
 */
class DonationReceived extends Mailable implements ShouldQueue
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
            subject: sprintf('New donation: %s to %s', $this->donation->formattedAmount(), $this->donation->fund_label),
            replyTo: [new Address($this->donation->donor_email, $this->donation->donor_name)],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.donation-received',
            with: [
                'paidOn' => $this->donation->paid_at?->setTimezone(config('chicd.timezone'))->format('j F Y, H:i'),
            ],
        );
    }
}
