<?php

use App\Enums\PostType;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->admin = User::factory()->admin()->create();
});

test('staff cannot manage news and events', function () {
    $this->actingAs(User::factory()->create())->get(route('admin.news.index'))->assertForbidden();
});

test('an admin publishes a story with a cover image', function () {
    $this->actingAs($this->admin)->post(route('admin.news.store'), [
        'title' => 'Uniforms delivered to Ikot Ekpene',
        'type' => 'story',
        'body' => "The first paragraph.\n\nThe second paragraph.",
        'cover_image' => UploadedFile::fake()->image('cover.jpg'),
        'cover_image_alt' => 'Pupils in new uniforms',
        'is_published' => true,
    ])->assertRedirect(route('admin.news.index'))->assertSessionHasNoErrors();

    $post = Post::sole();
    expect($post->type)->toBe(PostType::Story)
        ->and($post->slug)->toBe('uniforms-delivered-to-ikot-ekpene')
        ->and($post->isPublished())->toBeTrue();
    Storage::disk('public')->assertExists($post->cover_image_path);
});

test('two posts with the same title get different page addresses', function () {
    Post::factory()->create(['title' => 'Annual General Meeting', 'slug' => 'annual-general-meeting']);

    $this->actingAs($this->admin)->post(route('admin.news.store'), [
        'title' => 'Annual General Meeting',
        'type' => 'announcement',
        'body' => 'Details.',
    ])->assertSessionHasNoErrors();

    expect(Post::latest('id')->first()->slug)->toBe('annual-general-meeting-2');
});

test('an event start time is entered in Nigeria time', function () {
    $this->actingAs($this->admin)->post(route('admin.news.store'), [
        'title' => 'Community Health Fair',
        'type' => 'event',
        'body' => 'Join us.',
        'event_starts_at' => '2026-12-05T10:00',
        'event_location' => 'Uyo Township Stadium',
        'is_published' => true,
    ])->assertSessionHasNoErrors();

    $post = Post::sole();
    expect($post->event_starts_at->utc()->toDateTimeString())->toBe('2026-12-05 09:00:00')
        ->and($post->event_location)->toBe('Uyo Township Stadium');
});

test('an event needs a start time', function () {
    $this->actingAs($this->admin)->post(route('admin.news.store'), [
        'title' => 'Health Fair',
        'type' => 'event',
        'body' => 'Join us.',
    ])->assertSessionHasErrors(['event_starts_at' => 'Please enter when the event starts.']);
});

test('changing an event to another type clears its event details', function () {
    $post = Post::factory()->event()->create();

    $this->actingAs($this->admin)->put(route('admin.news.update', $post), [
        'title' => $post->title,
        'type' => 'news',
        'body' => $post->body,
        'event_starts_at' => '2026-12-05T10:00',
        'event_location' => 'Somewhere',
        'is_published' => true,
    ])->assertSessionHasNoErrors();

    expect($post->fresh())->event_starts_at->toBeNull()->event_location->toBeNull();
});

test('removing a post deletes its cover image', function () {
    Storage::disk('public')->put('news/cover.jpg', 'cover');
    $post = Post::factory()->create(['cover_image_path' => 'news/cover.jpg']);

    $this->actingAs($this->admin)->delete(route('admin.news.destroy', $post))->assertRedirect(route('admin.news.index'));

    expect(Post::count())->toBe(0);
    Storage::disk('public')->assertMissing('news/cover.jpg');
});
