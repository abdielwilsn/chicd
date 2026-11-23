<?php

use App\Models\TeamMember;
use Inertia\Testing\AssertableInertia as Assert;

test('the team page groups people by section in display order and leaves out hidden people', function () {
    $director = TeamMember::factory()->leadership()->create(['name' => 'Zara Bello', 'sort_order' => 1]);
    $chair = TeamMember::factory()->leadership()->create(['name' => 'Ada Okon', 'sort_order' => 2]);
    $officer = TeamMember::factory()->create(['name' => 'Emem Udoh']);
    TeamMember::factory()->unpublished()->create();

    $this->get(route('about.leadership'))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('site/about/Leadership')
        ->has('teamSections', 2)
        ->where('teamSections.0.section', 'leadership')
        ->where('teamSections.0.members.0.id', $director->id)
        ->where('teamSections.0.members.1.id', $chair->id)
        ->where('teamSections.0.members.1.initials', 'AO')
        ->where('teamSections.1.section', 'team')
        ->has('teamSections.1.members', 1)
        ->where('teamSections.1.members.0.id', $officer->id)
    );
});

test('a section with nobody in it is not shown', function () {
    TeamMember::factory()->create();

    $this->get(route('about.leadership'))->assertInertia(fn (Assert $page) => $page
        ->has('teamSections', 1)
        ->where('teamSections.0.section', 'team')
    );
});
