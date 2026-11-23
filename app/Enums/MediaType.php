<?php

namespace App\Enums;

/**
 * Whether a gallery item is a photograph or a video.
 */
enum MediaType: string
{
    case Photo = 'photo';
    case Video = 'video';

    /**
     * Human-readable label shown in the admin area.
     */
    public function label(): string
    {
        return match ($this) {
            self::Photo => 'Photo',
            self::Video => 'Video',
        };
    }
}
