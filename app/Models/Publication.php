<?php

namespace App\Models;

use App\Concerns\Publishable;
use App\Enums\PublicationCategory;
use Carbon\CarbonImmutable;
use Database\Factories\PublicationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
 * @property CarbonImmutable|null $published_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['title', 'category', 'year', 'summary', 'file_path', 'file_size', 'cover_image_path', 'is_featured', 'published_at'])]
class Publication extends Model
{
    /** @use HasFactory<PublicationFactory> */
    use HasFactory, Publishable;

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
}
