<?php

namespace App\Enums;

/**
 * Where a person is listed on the Leadership & Team page.
 */
enum TeamSection: string
{
    case Leadership = 'leadership';
    case Team = 'team';

    /**
     * Human-readable label shown on the website.
     */
    public function label(): string
    {
        return match ($this) {
            self::Leadership => 'Leadership',
            self::Team => 'Our Team',
        };
    }
}
