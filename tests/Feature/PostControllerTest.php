<?php

use App\Enums\PostType;
use App\Models\Post;
use Inertia\Testing\AssertableInertia as Assert;

test('News & Stories lists published posts, newest first', function () {
    $older = Post::factory()->create(['published_at' => now()->subDays(3)]);
    $newer = Post::factory()->create(['published_at' => now()->subDay()]);
    Post::factory()->unpublished()->create();
    Post::factory()->create(['published_at' => now()->addDay()]);

    $this->get(route('resources.news'))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('site/resources/News')
        ->has('posts', 2)
        ->where('posts.0.id', $newer->id)
        ->where('posts.1.id', $older->id)
    );
});

test('posts can be filtered by type', function () {
    $announcement = Post::factory()->create(['type' => PostType::Announcement]);
    Post::factory()->create(['type' => PostType::Story]);

    $this->get(route('resources.news', ['type' => 'announcement']))->assertInertia(fn (Assert $page) => $page
        ->has('posts', 1)
        ->where('posts.0.id', $announcement->id)
        ->where('filters.type', 'announcement')
    );
});

test('posts are shown twelve to a page', function () {
    Post::factory()->count(13)->create();

    $this->get(route('resources.news', ['page' => 2]))->assertInertia(fn (Assert $page) => $page
        ->has('posts', 1)
        ->where('pagination.currentPage', 2)
        ->where('pagination.lastPage', 2)
        ->where('pagination.nextUrl', null)
    );
});

test('upcoming events are listed soonest first, with times in Nigeria time', function () {
    $this->travelTo('2026-11-22 12:00:00');
    $later = Post::factory()->event('2026-12-10 09:00:00')->create();
    $sooner = Post::factory()->event('2026-11-30 09:00:00')->create();
    Post::factory()->event('2026-11-01 09:00:00')->create();

    $this->get(route('resources.news'))->assertInertia(fn (Assert $page) => $page
        ->has('upcomingEvents', 2)
        ->where('upcomingEvents.0.id', $sooner->id)
        ->where('upcomingEvents.0.event_starts', 'Monday 30 November 2026, 10:00 am')
        ->where('upcomingEvents.1.id', $later->id)
    );
});

test('a published post has its own page', function () {
    $post = Post::factory()->create(['body' => "One.\n\nTwo."]);

    $this->get(route('resources.post', $post->slug))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('site/resources/Post')
        ->where('post.id', $post->id)
        ->where('post.body', ['One.', 'Two.'])
    );
});

test('a draft post has no public page', function () {
    $post = Post::factory()->unpublished()->create();

    $this->get(route('resources.post', $post->slug))->assertNotFound();
});
