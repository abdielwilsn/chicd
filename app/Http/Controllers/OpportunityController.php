<?php

namespace App\Http\Controllers;

use App\Enums\OpportunityType;
use App\Http\Resources\OpportunityResource;
use App\Models\Opportunity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OpportunityController extends Controller
{
    /**
     * List open opportunities, optionally of one type, closing soonest first.
     */
    public function index(Request $request): Response
    {
        $type = $request->enum('type', OpportunityType::class);

        return Inertia::render('site/involved/Opportunities', [
            'opportunities' => OpportunityResource::collection(
                Opportunity::query()
                    ->published()
                    ->open()
                    ->when($type, fn (Builder $query) => $query->where('type', $type))
                    ->orderByRaw('closes_on is null')
                    ->orderBy('closes_on')
                    ->latest('published_at')
                    ->get()
            )->resolve(),
            'filters' => ['type' => $type?->value],
            'typeOptions' => array_map(fn (OpportunityType $option): array => [
                'value' => $option->value,
                'label' => $option->label(),
            ], OpportunityType::cases()),
        ]);
    }

    /**
     * Show one opportunity. Closed opportunities stay viewable, marked as
     * closed, so shared links do not break.
     */
    public function show(Opportunity $opportunity): Response
    {
        abort_unless($opportunity->isPublished(), 404);

        return Inertia::render('site/involved/Opportunity', [
            'opportunity' => OpportunityResource::make($opportunity)->resolve(),
        ]);
    }

    /**
     * Show the Volunteer page with any open volunteer roles.
     */
    public function volunteer(): Response
    {
        return Inertia::render('site/involved/Volunteer', [
            'opportunities' => OpportunityResource::collection(
                Opportunity::query()
                    ->published()
                    ->open()
                    ->where('type', OpportunityType::Volunteer)
                    ->orderByRaw('closes_on is null')
                    ->orderBy('closes_on')
                    ->get()
            )->resolve(),
        ]);
    }
}
