<?php

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
