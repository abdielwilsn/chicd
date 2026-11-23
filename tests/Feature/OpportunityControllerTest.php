<?php

use App\Enums\OpportunityType;
use App\Models\Opportunity;
use Inertia\Testing\AssertableInertia as Assert;

test('only published, open opportunities are listed, closing soonest first', function () {
    $this->travelTo('2026-11-22 12:00:00');
    $later = Opportunity::factory()->create(['closes_on' => '2026-12-20']);
    $noDeadline = Opportunity::factory()->create(['closes_on' => null]);
    $closingToday = Opportunity::factory()->create(['closes_on' => '2026-11-22']);
    Opportunity::factory()->create(['closes_on' => '2026-11-21']);
    Opportunity::factory()->unpublished()->create();

    $this->get(route('get-involved.opportunities'))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('site/involved/Opportunities')
        ->has('opportunities', 3)
        ->where('opportunities.0.id', $closingToday->id)
        ->where('opportunities.1.id', $later->id)
        ->where('opportunities.2.id', $noDeadline->id)
    );
});

test('opportunities can be filtered by type', function () {
    $vacancy = Opportunity::factory()->create(['type' => OpportunityType::Vacancy]);
    Opportunity::factory()->create(['type' => OpportunityType::Internship]);

    $this->get(route('get-involved.opportunities', ['type' => 'vacancy']))->assertInertia(fn (Assert $page) => $page
        ->has('opportunities', 1)
        ->where('opportunities.0.id', $vacancy->id)
        ->where('filters.type', 'vacancy')
    );
});

test('a closed opportunity can still be viewed, marked as closed', function () {
    $opportunity = Opportunity::factory()->closed()->create();

    $this->get(route('get-involved.opportunity', $opportunity->slug))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('site/involved/Opportunity')
        ->where('opportunity.is_closed', true)
    );
});

test('a draft opportunity has no public page', function () {
    $opportunity = Opportunity::factory()->unpublished()->create();

    $this->get(route('get-involved.opportunity', $opportunity->slug))->assertNotFound();
});

test('the volunteer page lists open volunteer roles only', function () {
    $role = Opportunity::factory()->create(['type' => OpportunityType::Volunteer]);
    Opportunity::factory()->closed()->create(['type' => OpportunityType::Volunteer]);
    Opportunity::factory()->create(['type' => OpportunityType::Vacancy]);

    $this->get(route('get-involved.volunteer'))->assertInertia(fn (Assert $page) => $page
        ->component('site/involved/Volunteer')
        ->has('opportunities', 1)
        ->where('opportunities.0.id', $role->id)
    );
});
