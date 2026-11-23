<?php

namespace App\Enums;

/**
 * The kinds of opportunity CHICD advertises on Get Involved.
 */
enum OpportunityType: string
{
    case Volunteer = 'volunteer';
    case Research = 'research';
    case Internship = 'internship';
    case Vacancy = 'vacancy';
    case Consultancy = 'consultancy';

    /**
     * Human-readable label shown on the website.
     */
    public function label(): string
    {
        return match ($this) {
            self::Volunteer => 'Volunteering',
            self::Research => 'Research',
            self::Internship => 'Internship',
            self::Vacancy => 'Vacancy',
            self::Consultancy => 'Consultancy',
        };
    }
}
