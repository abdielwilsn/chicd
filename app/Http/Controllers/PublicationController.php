<?php

namespace App\Http\Controllers;

use App\Enums\PublicationCategory;
use App\Http\Resources\PublicationResource;
use App\Models\Publication;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PublicationController extends Controller
{
    /**
     * Show the Publications & Reports library, optionally searched and
     * filtered by category and year. The featured annual report is shown only
     * when nothing is being searched for.
     */
    public function index(Request $request): Response
    {
        $search = trim($request->string('search')->limit(100, '')->toString());
        $category = $request->enum('category', PublicationCategory::class);
        $year = $request->integer('year') ?: null;
        $isFiltered = $search !== '' || $category !== null || $year !== null;

        $publications = Publication::query()
            ->published()
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $query) => $query
                ->whereLike('title', "%{$search}%")
                ->orWhereLike('summary', "%{$search}%")))
            ->when($category, fn (Builder $query) => $query->where('category', $category))
            ->when($year, fn (Builder $query) => $query->where('year', $year))
            ->latest('year')
            ->latest('published_at')
            ->get();

        return Inertia::render('site/resources/Publications', [
            'featuredAnnualReport' => $isFiltered ? null : $this->featuredAnnualReport($publications),
            'groups' => $this->groupByCategory($publications, PublicationCategory::cases()),
            'filters' => [
                'search' => $search,
                'category' => $category?->value,
                'year' => $year,
            ],
            'categoryOptions' => array_map(fn (PublicationCategory $option): array => [
                'value' => $option->value,
                'label' => $option->label(),
            ], PublicationCategory::cases()),
            'yearOptions' => Publication::query()->published()->distinct()->orderByDesc('year')->pluck('year')->all(),
        ]);
    }

    /**
     * Show the Policies & Accountability page.
     */
    public function accountability(): Response
    {
        $publications = Publication::query()
            ->published()
            ->whereIn('category', PublicationCategory::accountability())
            ->latest('year')
            ->latest('published_at')
            ->get();

        return Inertia::render('site/policies/Accountability', [
            'featuredAnnualReport' => $this->featuredAnnualReport($publications),
            'groups' => $this->groupByCategory($publications, PublicationCategory::accountability()),
        ]);
    }

    /**
     * Show Research & Insights with the latest research outputs, briefs,
     * evaluations and toolkits.
     */
    public function research(): Response
    {
        return Inertia::render('site/resources/Research', [
            'publications' => PublicationResource::collection(
                Publication::query()
                    ->published()
                    ->whereIn('category', PublicationCategory::research())
                    ->latest('year')
                    ->latest('published_at')
                    ->limit(6)
                    ->get()
            )->resolve(),
        ]);
    }

    /**
     * The annual report chosen for display, falling back to the most recent one.
     *
     * @param  Collection<int, Publication>  $publications
     * @return array<string, mixed>|null
     */
    private function featuredAnnualReport(Collection $publications): ?array
    {
        $annualReports = $publications->where('category', PublicationCategory::AnnualReport);

        $report = $annualReports->firstWhere('is_featured', true) ?? $annualReports->first();

        return $report ? PublicationResource::make($report)->resolve() : null;
    }

    /**
     * Group publications by category, in the given category order, omitting empty groups.
     *
     * @param  Collection<int, Publication>  $publications
     * @param  list<PublicationCategory>  $categories
     * @return list<array{category: string, label: string, items: array<int, mixed>}>
     */
    private function groupByCategory(Collection $publications, array $categories): array
    {
        return array_values(collect($categories)
            ->map(fn (PublicationCategory $category) => [
                'category' => $category->value,
                'label' => $category->label(),
                'items' => PublicationResource::collection(
                    $publications->where('category', $category)->values()
                )->resolve(),
            ])
            ->filter(fn (array $group) => $group['items'] !== [])
            ->all());
    }
}
