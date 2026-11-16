<?php

namespace App\Models;

use App\Enums\PublicationCategory;
use Database\Factories\PublicationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * A downloadable document: an annual report, research output or governance policy.
 *
 * @property int $id
 * @property string $title
 * @property PublicationCategory $category
 * @property int $year
 * @property string|null $summary
 * @property string $file_path
 * @property int|null $file_size
 * @property string|null $cover_image_path
 * @property bool $is_featured
 * @property Carbon|null $published_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['title', 'category', 'year', 'summary', 'file_path', 'file_size', 'cover_image_path', 'is_featured', 'published_at'])]
class Publication extends Model
{
    /** @use HasFactory<PublicationFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => PublicationCategory::class,
            'year' => 'integer',
            'file_size' => 'integer',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Only publications that have been published and are visible to the public.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }
}
