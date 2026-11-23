<?php

namespace App\Concerns;

use Illuminate\Support\Str;

/**
 * Gives a model a unique URL slug, made from its title when it is first
 * saved. The slug then stays the same so shared links keep working.
 */
trait HasUniqueSlug
{
    /**
     * The attribute the slug is made from.
     */
    abstract protected function slugSource(): string;

    /**
     * Fill in the slug before the record is first saved.
     */
    protected static function bootHasUniqueSlug(): void
    {
        static::creating(function (self $model): void {
            if (blank($model->slug)) {
                $model->slug = $model->uniqueSlugFor($model->{$model->slugSource()});
            }
        });
    }

    /**
     * A slug for the given text that no other record uses.
     */
    private function uniqueSlugFor(string $text): string
    {
        $base = Str::slug(Str::limit($text, 80, '')) ?: Str::lower(class_basename($this));
        $slug = $base;

        for ($suffix = 2; static::query()->where('slug', $slug)->exists(); $suffix++) {
            $slug = "{$base}-{$suffix}";
        }

        return $slug;
    }
}
