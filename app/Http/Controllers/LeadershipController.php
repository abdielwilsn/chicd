<?php

namespace App\Http\Controllers;

use App\Enums\TeamSection;
use App\Http\Resources\TeamMemberResource;
use App\Models\TeamMember;
use Inertia\Inertia;
use Inertia\Response;

class LeadershipController extends Controller
{
    /**
     * Show the Leadership & Team page with the people currently listed,
     * grouped by section.
     */
    public function __invoke(): Response
    {
        $members = TeamMember::query()->published()->ordered()->get();

        return Inertia::render('site/about/Leadership', [
            'teamSections' => array_values(collect(TeamSection::cases())
                ->map(fn (TeamSection $section): array => [
                    'section' => $section->value,
                    'label' => $section->label(),
                    'members' => TeamMemberResource::collection(
                        $members->where('section', $section)->values()
                    )->resolve(),
                ])
                ->filter(fn (array $group): bool => $group['members'] !== [])
                ->all()),
        ]);
    }
}
