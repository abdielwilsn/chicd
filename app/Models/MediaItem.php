<?php

namespace App\Models;

use App\Enums\MediaType;
use Carbon\CarbonImmutable;
use Database\Factories\MediaItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A photograph or video in the Media Gallery, optionally shown in the home
 * page hero.
 *
 * @property int $id
 * @property MediaType $type
 * @property string|null $caption
 * @property string|null $alt_text
 * @property string $file_path
 * @property int|null $file_size
 * @property string|null $poster_path
 * @property bool $show_in_hero
 * @property int $sort_order
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['caption', 'alt_text', 'show_in_hero', 'sort_order'])]
class MediaItem extends Model
{
    /** @use HasFactory<MediaItemFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => MediaType::class,
            'file_size' => 'integer',
            'show_in_hero' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Gallery order: the admin's chosen order, then newest first.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->latest('id');
    }
}
