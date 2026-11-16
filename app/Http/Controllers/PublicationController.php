<?php

namespace App\Http\Controllers;

use App\Enums\PublicationCategory;
use App\Http\Resources\PublicationResource;
use App\Models\Publication;
use Illuminate\Database\Eloquent\Collection;
use Inertia\Inertia;
use Inertia\Response;

class PublicationController extends Controller
{
    /**
     * Show the Publications & Reports library.
     */
    public function index(): Response
    {
        $publications = Publication::query()->published()->latest('year')->latest('published_at')->get();

        return Inertia::render('site/resources/Publications', [
            'featuredAnnualReport' => $this->featuredAnnualReport($publications),
            'groups' => $this->groupByCategory($publications, PublicationCategory::cases()),
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
