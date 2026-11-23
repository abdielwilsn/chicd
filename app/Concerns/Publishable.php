<?php

namespace App\Concerns;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

/**
 * Website content that is either published or kept as a draft, using a
 * nullable published_at timestamp.
 */
trait Publishable
{
    /**
     * Only records that have been published and are visible to the public.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    /**
     * Whether visitors can see this record.
     */
    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->isPast();
    }

    /**
     * Publish or unpublish the record. Re-saving published content keeps its
     * original publish date.
     */
    public function publishWhen(bool $shouldPublish): void
    {
        $this->published_at = match (true) {
            ! $shouldPublish => null,
            $this->isPublished() => $this->published_at,
            default => now(),
        };
    }
}
