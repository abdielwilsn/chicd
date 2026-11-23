<?php

use App\Models\MediaItem;
use Inertia\Testing\AssertableInertia as Assert;

test('the gallery shows every photograph and video in display order', function () {
    $second = MediaItem::factory()->video()->create(['sort_order' => 2]);
    $first = MediaItem::factory()->create(['sort_order' => 1]);

    $this->get(route('resources.gallery'))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('site/resources/Gallery')
        ->has('items', 2)
        ->where('items.0.id', $first->id)
        ->where('items.1.id', $second->id)
        ->where('items.1.type', 'video')
    );
});

test('the home page hero shows only the items chosen for it', function () {
    $hero = MediaItem::factory()->inHero()->create();
    MediaItem::factory()->create();

    $this->get(route('home'))->assertInertia(fn (Assert $page) => $page
        ->has('heroMedia', 1)
        ->where('heroMedia.0.id', $hero->id)
    );
});
