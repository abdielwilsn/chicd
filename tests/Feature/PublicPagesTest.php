<?php

use App\Models\Programme;
use Inertia\Testing\AssertableInertia as Assert;

test('each public page renders its page component', function (string $routeName, string $component) {
    $response = $this->get(route($routeName));

    $response->assertOk()->assertInertia(fn (Assert $page) => $page->component($component));
})->with([
    ['home', 'site/Home'],
    ['about.index', 'site/about/Index'],
    ['about.story', 'site/about/Story'],
    ['about.values', 'site/about/Values'],
    ['about.leadership', 'site/about/Leadership'],
    ['work.index', 'site/work/Index'],
    ['work.surl', 'site/work/Surl'],
    ['work.safe-housing', 'site/work/SafeHousing'],
    ['resources.research', 'site/resources/Research'],
    ['resources.news', 'site/resources/News'],
    ['get-involved.index', 'site/involved/Index'],
    ['get-involved.partnerships', 'site/involved/Partnerships'],
    ['get-involved.volunteer', 'site/involved/Volunteer'],
    ['get-involved.support', 'site/involved/Support'],
    ['contact', 'site/Contact'],
    ['donate', 'site/Donate'],
    ['policies.feedback', 'site/policies/Feedback'],
]);

test('each policy summary page renders the matching policy', function (string $routeName, string $policy) {
    $response = $this->get(route($routeName));

    $response->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('site/policies/Policy')
        ->where('policy', $policy)
    );
})->with([
    ['policies.privacy', 'privacy'],
    ['policies.safeguarding', 'safeguarding'],
    ['policies.terms', 'terms'],
]);

test('only configured social networks are shared with the website', function () {
    config([
        'chicd.social' => [
            'facebook' => 'https://facebook.com/chicd',
            'instagram' => null,
            'linkedin' => null,
        ],
    ]);

    $response = $this->get(route('home'));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('site.social', ['facebook' => 'https://facebook.com/chicd'])
    );
});

test('published added programmes are shared in order for the Our Work menu', function () {
    $second = Programme::factory()->create(['name' => 'Clean Water Programme', 'tagline' => 'Water for every school', 'sort_order' => 2]);
    $first = Programme::factory()->create(['name' => 'Mobile Clinic Programme', 'tagline' => null, 'sort_order' => 1]);
    Programme::factory()->unpublished()->create();

    $response = $this->get(route('contact'));

    $response->assertInertia(fn (Assert $page) => $page
        ->has('site.programmes', 2)
        ->where('site.programmes.0.title', 'Mobile Clinic Programme')
        ->where('site.programmes.0.href', "/our-work/programmes/{$first->slug}")
        ->where('site.programmes.0.description', null)
        ->where('site.programmes.1.title', 'Clean Water Programme')
        ->where('site.programmes.1.description', 'Water for every school')
        ->where('site.programmes.1.image.alt', $second->image_alt)
    );
});
