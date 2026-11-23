<?php

namespace App\Models;

use App\Concerns\HasUniqueSlug;
use App\Concerns\Publishable;
use App\Enums\OpportunityType;
use Carbon\CarbonImmutable;
use Database\Factories\OpportunityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A volunteer role, research opportunity, internship, vacancy or consultancy.
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property OpportunityType $type
 * @property string|null $location
 * @property string $summary
 * @property string|null $body
 * @property CarbonImmutable|null $closes_on
 * @property string|null $application_url
 * @property string|null $attachment_path
 * @property int|null $attachment_size
 * @property CarbonImmutable|null $published_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[Fillable(['title', 'type', 'location', 'summary', 'body', 'closes_on', 'application_url'])]
class Opportunity extends Model
{
    /** @use HasFactory<OpportunityFactory> */
    use HasFactory, HasUniqueSlug, Publishable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => OpportunityType::class,
            'closes_on' => 'date',
            'attachment_size' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    protected function slugSource(): string
    {
        return 'title';
    }

    /**
     * Whether the closing date has passed in the organisation timezone. An
     * opportunity with no closing date stays open until it is removed.
     */
    public function isClosed(): bool
    {
        return $this->closes_on !== null
            && $this->closes_on->toDateString() < now(config('chicd.timezone'))->toDateString();
    }

    /**
     * Opportunities still accepting applications.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function open(Builder $query): void
    {
        $query->where(fn (Builder $query) => $query
            ->whereNull('closes_on')
            ->orWhereDate('closes_on', '>=', now(config('chicd.timezone'))->toDateString()));
    }
}
