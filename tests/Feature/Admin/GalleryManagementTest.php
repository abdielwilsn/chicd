<?php

use App\Enums\MediaType;
use App\Models\MediaItem;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->admin = User::factory()->admin()->create();
});

test('staff cannot manage the gallery', function () {
    $this->actingAs(User::factory()->create())->get(route('admin.gallery.index'))->assertForbidden();
});

test('an admin adds a photograph for the home page hero', function () {
    $this->actingAs($this->admin)->post(route('admin.gallery.store'), [
        'type' => 'photo',
        'file' => UploadedFile::fake()->image('outreach.jpg', 1600, 900),
        'alt_text' => 'A nurse talking with mothers',
        'caption' => 'Health outreach in Uyo',
        'show_in_hero' => true,
    ])->assertRedirect(route('admin.gallery.index'))->assertSessionHasNoErrors();

    $item = MediaItem::sole();
    expect($item->type)->toBe(MediaType::Photo)
        ->and($item->show_in_hero)->toBeTrue();
    Storage::disk('public')->assertExists($item->file_path);
});

test('a photograph needs a description', function () {
    $this->actingAs($this->admin)->post(route('admin.gallery.store'), [
        'type' => 'photo',
        'file' => UploadedFile::fake()->image('outreach.jpg'),
    ])->assertSessionHasErrors(['alt_text' => 'Describe the photo for people who cannot see it.']);
});

test('an admin uploads a video with a poster image', function () {
    $this->actingAs($this->admin)->post(route('admin.gallery.store'), [
        'type' => 'video',
        'file' => UploadedFile::fake()->create('event.mp4', 2000, 'video/mp4'),
        'poster' => UploadedFile::fake()->image('poster.jpg'),
    ])->assertSessionHasNoErrors();

    $item = MediaItem::sole();
    expect($item->type)->toBe(MediaType::Video);
    Storage::disk('public')->assertExists([$item->file_path, $item->poster_path]);
});

test('a video must be an MP4 or WebM file', function () {
    $this->actingAs($this->admin)->post(route('admin.gallery.store'), [
        'type' => 'video',
        'file' => UploadedFile::fake()->image('not-a-video.jpg'),
    ])->assertSessionHasErrors('file');

    expect(MediaItem::count())->toBe(0);
});

test('editing an item changes its details but not its file', function () {
    $item = MediaItem::factory()->create(['file_path' => 'gallery/photo.jpg']);

    $this->actingAs($this->admin)->put(route('admin.gallery.update', $item), [
        'alt_text' => 'New description',
        'show_in_hero' => true,
        'sort_order' => 3,
    ])->assertSessionHasNoErrors();

    expect($item->fresh())
        ->alt_text->toBe('New description')
        ->show_in_hero->toBeTrue()
        ->sort_order->toBe(3)
        ->file_path->toBe('gallery/photo.jpg');

    $this->actingAs($this->admin)->put(route('admin.gallery.update', $item), [
        'alt_text' => 'New description',
        'file' => UploadedFile::fake()->image('other.jpg'),
    ])->assertSessionHasErrors('file');
});

test('removing an item deletes its files', function () {
    Storage::disk('public')->put('gallery/video.mp4', 'video');
    Storage::disk('public')->put('gallery/posters/poster.jpg', 'poster');
    $item = MediaItem::factory()->video()->create(['file_path' => 'gallery/video.mp4', 'poster_path' => 'gallery/posters/poster.jpg']);

    $this->actingAs($this->admin)->delete(route('admin.gallery.destroy', $item))->assertRedirect(route('admin.gallery.index'));

    expect(MediaItem::count())->toBe(0);
    Storage::disk('public')->assertMissing(['gallery/video.mp4', 'gallery/posters/poster.jpg']);
});
