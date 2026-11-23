<?php

namespace App\Enums;

/**
 * The kinds of article published on News & Stories.
 */
enum PostType: string
{
    case Story = 'story';
    case News = 'news';
    case Event = 'event';
    case Announcement = 'announcement';

    /**
     * Human-readable label shown on the website.
     */
    public function label(): string
    {
        return match ($this) {
            self::Story => 'Story',
            self::News => 'News',
            self::Event => 'Event',
            self::Announcement => 'Announcement',
        };
    }

    /**
     * Plural label used for listing filters.
     */
    public function pluralLabel(): string
    {
        return match ($this) {
            self::Story => 'Stories',
            self::News => 'News',
            self::Event => 'Events',
            self::Announcement => 'Announcements',
        };
    }
}
