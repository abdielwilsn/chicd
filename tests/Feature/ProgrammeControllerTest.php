<?php

use App\Models\Post;
use App\Models\Programme;
use Inertia\Testing\AssertableInertia as Assert;

test('added programmes appear on the home page and Our Work in order, drafts hidden', function (string $routeName) {
    $second = Programme::factory()->create(['sort_order' => 2]);
    $first = Programme::factory()->create(['sort_order' => 1]);
    Programme::factory()->unpublished()->create();

    $this->get(route($routeName))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->has('programmes', 2)
        ->where('programmes.0.id', $first->id)
        ->where('programmes.0.url', "/our-work/programmes/{$first->slug}")
        ->where('programmes.1.id', $second->id)
    );
})->with(['home', 'work.index']);

test('a programme summary is split into paragraphs', function () {
    Programme::factory()->create(['summary' => "First paragraph.\n\n\nSecond paragraph.\n"]);

    $this->get(route('work.index'))->assertInertia(fn (Assert $page) => $page
        ->where('programmes.0.summary', ['First paragraph.', 'Second paragraph.'])
    );
});

test('Our Work shows the three latest published updates', function () {
    Post::factory()->create(['published_at' => now()->subDays(4)]);
    $latest = Post::factory()->create(['published_at' => now()->subDay()]);
    Post::factory()->count(2)->create(['published_at' => now()->subDays(2)]);
    Post::factory()->unpublished()->create();

    $this->get(route('work.index'))->assertInertia(fn (Assert $page) => $page
        ->has('latestPosts', 3)
        ->where('latestPosts.0.id', $latest->id)
    );
});

test('a published programme has its own page', function () {
    $programme = Programme::factory()->create();

    $this->get(route('work.programme', $programme->slug))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('site/work/Programme')
        ->where('programme.id', $programme->id)
    );
});

test('a draft programme has no public page', function () {
    $programme = Programme::factory()->unpublished()->create();

    $this->get(route('work.programme', $programme->slug))->assertNotFound();
});
