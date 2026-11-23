<?php

namespace App\Models;

use App\Concerns\HasUniqueSlug;
use App\Concerns\Publishable;
use Carbon\CarbonImmutable;
use Database\Factories\ProgrammeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A programme added by an admin. It is listed after the flagship programmes
 * (SURL and Safe Housing, which have their own designed pages) and gets a
 * standard programme page.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $tagline
 * @property string $summary
 * @property string|null $body
 * @property string $image_path
 * @property string|null $image_alt
 * @property int $sort_order
 * @property CarbonImmutable|null $published_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['name', 'tagline', 'summary', 'body', 'image_alt', 'sort_order'])]
class Programme extends Model
{
    /** @use HasFactory<ProgrammeFactory> */
    use HasFactory, HasUniqueSlug, Publishable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    protected function slugSource(): string
    {
        return 'name';
    }

    /**
     * Website order: the admin's chosen order, then alphabetical.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name');
    }
}
