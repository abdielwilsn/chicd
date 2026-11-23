<?php

use App\Enums\PublicationCategory;
use App\Models\Publication;
use Inertia\Testing\AssertableInertia as Assert;

test('the library groups published documents by category and hides unpublished ones', function () {
    $research = Publication::factory()->create([
        'category' => PublicationCategory::Research,
        'title' => 'Barriers to School Attendance in Rural Akwa Ibom',
    ]);
    $toolkit = Publication::factory()->create(['category' => PublicationCategory::Toolkit]);
    Publication::factory()->unpublished()->create(['category' => PublicationCategory::Research]);

    $response = $this->get(route('resources.publications'));

    $response->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('site/resources/Publications')
        ->where('featuredAnnualReport', null)
        ->has('groups', 2)
        ->where('groups.0.category', 'research')
        ->where('groups.0.label', 'Research')
        ->has('groups.0.items', 1)
        ->where('groups.0.items.0.id', $research->id)
        ->where('groups.0.items.0.title', 'Barriers to School Attendance in Rural Akwa Ibom')
        ->where('groups.1.category', 'toolkit')
        ->where('groups.1.items.0.id', $toolkit->id)
    );
});

test('the annual report chosen by an admin is featured', function () {
    Publication::factory()->annualReport()->create(['year' => 2028]);
    $featured = Publication::factory()->annualReport()->featured()->create(['year' => 2027]);

    $response = $this->get(route('resources.publications'));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('featuredAnnualReport.id', $featured->id)
    );
});

test('the most recent annual report is featured when none has been chosen', function () {
    Publication::factory()->annualReport()->create(['year' => 2027]);
    $latest = Publication::factory()->annualReport()->create(['year' => 2028]);

    $response = $this->get(route('resources.publications'));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('featuredAnnualReport.id', $latest->id)
    );
});

test('the accountability page only lists reports and governance documents', function () {
    $policy = Publication::factory()->governance()->create(['title' => 'Safeguarding Policy']);
    $report = Publication::factory()->annualReport()->create(['year' => 2027]);
    Publication::factory()->create(['category' => PublicationCategory::Research]);

    $response = $this->get(route('policies.index'));

    $response->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('site/policies/Accountability')
        ->where('featuredAnnualReport.id', $report->id)
        ->has('groups', 2)
        ->where('groups.0.category', 'annual_report')
        ->where('groups.1.category', 'governance')
        ->where('groups.1.items.0.id', $policy->id)
    );
});

test('visitors can search the library by title or summary', function () {
    $match = Publication::factory()->create(['title' => 'School Uniforms and Attendance', 'summary' => 'Findings.']);
    $summaryMatch = Publication::factory()->create(['title' => 'Field notes', 'summary' => 'On uniforms in rural schools.']);
    Publication::factory()->create(['title' => 'Housing toolkit', 'summary' => 'Shelter guidance.']);

    $response = $this->get(route('resources.publications', ['search' => 'uniform']));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('filters.search', 'uniform')
        ->where('groups', fn ($groups) => collect($groups)->flatMap(fn ($group) => collect($group['items'])->pluck('id'))->sort()->values()->all() === collect([$match->id, $summaryMatch->id])->sort()->values()->all())
    );
});

test('visitors can filter the library by category and year, and the featured report is hidden while filtering', function () {
    Publication::factory()->annualReport()->featured()->create(['year' => 2026]);
    $research2027 = Publication::factory()->create(['category' => PublicationCategory::Research, 'year' => 2027]);
    Publication::factory()->create(['category' => PublicationCategory::Research, 'year' => 2026]);
    Publication::factory()->unpublished()->create(['year' => 2030]);

    $response = $this->get(route('resources.publications', ['category' => 'research', 'year' => 2027]));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('featuredAnnualReport', null)
        ->has('groups', 1)
        ->has('groups.0.items', 1)
        ->where('groups.0.items.0.id', $research2027->id)
        ->where('filters.category', 'research')
        ->where('filters.year', 2027)
        ->where('yearOptions', [2027, 2026])
    );
});

test('Research & Insights lists the latest research outputs only', function () {
    $brief = Publication::factory()->create(['category' => PublicationCategory::PolicyBrief]);
    Publication::factory()->governance()->create();
    Publication::factory()->unpublished()->create(['category' => PublicationCategory::Research]);

    $this->get(route('resources.research'))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('site/resources/Research')
        ->has('publications', 1)
        ->where('publications.0.id', $brief->id)
    );
});
