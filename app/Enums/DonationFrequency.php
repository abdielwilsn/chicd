<?php

namespace App\Enums;

/**
 * How often a donor gives. Recurring gifts are Paystack subscriptions on a
 * plan with the matching interval.
 */
enum DonationFrequency: string
{
    case OneTime = 'one_time';
    case Monthly = 'monthly';
    case Annually = 'annually';

    /**
     * Human-readable label shown to donors and admins.
     */
    public function label(): string
    {
        return match ($this) {
            self::OneTime => 'One-time',
            self::Monthly => 'Monthly',
            self::Annually => 'Yearly',
        };
    }

    /**
     * Whether Paystack charges the donor again on a schedule.
     */
    public function isRecurring(): bool
    {
        return $this !== self::OneTime;
    }

    /**
     * The Paystack plan interval for a recurring gift.
     */
    public function paystackInterval(): ?string
    {
        return match ($this) {
            self::OneTime => null,
            self::Monthly => 'monthly',
            self::Annually => 'annually',
        };
    }
}
