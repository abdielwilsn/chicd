<?php

namespace App\Enums;

/**
 * Where a donation is with Paystack. A donation starts Pending when the donor
 * is sent to Paystack. Failed and Abandoned donations can still become
 * Successful if Paystack later confirms the payment.
 */
enum DonationStatus: string
{
    case Pending = 'pending';
    case Successful = 'successful';
    case Failed = 'failed';
    case Abandoned = 'abandoned';

    /**
     * Human-readable label shown to donors and admins.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Awaiting payment',
            self::Successful => 'Successful',
            self::Failed => 'Failed',
            self::Abandoned => 'Not completed',
        };
    }

    /**
     * The status for a transaction status reported by Paystack, or null when
     * the payment is still in progress.
     */
    public static function fromPaystack(string $status): ?self
    {
        return match ($status) {
            'success' => self::Successful,
            'failed', 'reversed' => self::Failed,
            'abandoned' => self::Abandoned,
            default => null,
        };
    }
}
