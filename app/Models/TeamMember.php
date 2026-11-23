<?php

namespace App\Models;

use App\Concerns\Publishable;
use App\Enums\TeamSection;
use Carbon\CarbonImmutable;
use Database\Factories\TeamMemberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A person shown on the Leadership & Team page.
 *
 * @property int $id
 * @property string $name
 * @property string $position
 * @property TeamSection $section
 * @property string|null $bio
 * @property string|null $photo_path
 * @property int $sort_order
 * @property CarbonImmutable|null $published_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['name', 'position', 'section', 'bio', 'sort_order'])]
class TeamMember extends Model
{
    /** @use HasFactory<TeamMemberFactory> */
    use HasFactory, Publishable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'section' => TeamSection::class,
            'sort_order' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Page order: the admin's chosen order, then alphabetical.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name');
    }
}
