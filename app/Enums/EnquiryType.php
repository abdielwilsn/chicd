<?php

namespace App\Enums;

enum EnquiryType: string
{
    case General = 'general';
    case Partnership = 'partnership';
    case Volunteer = 'volunteer';
    case Research = 'research';
    case Donation = 'donation';
    case SafeHousing = 'safe_housing';
    case Feedback = 'feedback';

    /**
     * Human-readable label used in notifications.
     */
    public function label(): string
    {
        return match ($this) {
            self::General => 'General enquiry',
            self::Partnership => 'Partnership enquiry',
            self::Volunteer => 'Volunteer application',
            self::Research => 'Research collaboration',
            self::Donation => 'Donation enquiry',
            self::SafeHousing => 'Confidential Safe Housing enquiry',
            self::Feedback => 'Complaint or feedback',
        };
    }

    /**
     * Whether the enquiry contains sensitive personal circumstances.
     */
    public function isConfidential(): bool
    {
        return in_array($this, [self::SafeHousing, self::Feedback], true);
    }
}
