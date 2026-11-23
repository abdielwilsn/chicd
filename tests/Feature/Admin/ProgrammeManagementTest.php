<?php

use App\Models\Programme;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('public');
    $this->admin = User::factory()->admin()->create();
});

test('staff cannot manage programmes', function () {
    $this->actingAs(User::factory()->create())->get(route('admin.programmes.index'))->assertForbidden();
});

test('admins see every programme, drafts included, in website order', function () {
    $second = Programme::factory()->unpublished()->create(['sort_order' => 2]);
    $first = Programme::factory()->create(['sort_order' => 1]);

    $this->actingAs($this->admin)->get(route('admin.programmes.index'))->assertInertia(fn (Assert $page) => $page
        ->component('admin/programmes/Index')
        ->where('programmes.0.id', $first->id)
        ->where('programmes.1.id', $second->id)
        ->where('programmes.1.is_published', false)
    );
});

test('an admin adds a programme with a photograph and it gets a page address', function () {
    $this->travelTo('2026-11-22 10:00:00');

    $this->actingAs($this->admin)->post(route('admin.programmes.store'), [
        'name' => 'Community Health Outreach',
        'summary' => 'Bringing health education to rural communities.',
        'image' => UploadedFile::fake()->image('outreach.jpg'),
        'image_alt' => 'Health workers with community members',
        'is_published' => true,
    ])->assertRedirect(route('admin.programmes.index'))->assertSessionHasNoErrors();

    $programme = Programme::sole();
    expect($programme->slug)->toBe('community-health-outreach')
        ->and($programme->published_at->toDateTimeString())->toBe('2026-11-22 10:00:00');
    Storage::disk('public')->assertExists($programme->image_path);
});

test('a new programme needs a name, summary and photograph', function () {
    $this->actingAs($this->admin)->post(route('admin.programmes.store'), [])
        ->assertSessionHasErrors(['name', 'summary', 'image' => 'Please choose a photograph for the programme.']);

    expect(Programme::count())->toBe(0);
});

test('replacing the photograph removes the old file and keeps the page address', function () {
    Storage::disk('public')->put('programmes/old.jpg', 'old');
    $programme = Programme::factory()->create(['name' => 'Original name', 'slug' => 'original-name', 'image_path' => 'programmes/old.jpg']);

    $this->actingAs($this->admin)->put(route('admin.programmes.update', $programme), [
        'name' => 'Renamed programme',
        'summary' => $programme->summary,
        'image' => UploadedFile::fake()->image('new.jpg'),
        'is_published' => true,
    ])->assertSessionHasNoErrors();

    $programme->refresh();
    expect($programme->name)->toBe('Renamed programme')
        ->and($programme->slug)->toBe('original-name');
    Storage::disk('public')->assertMissing('programmes/old.jpg');
    Storage::disk('public')->assertExists($programme->image_path);
});

test('removing a programme deletes its photograph', function () {
    Storage::disk('public')->put('programmes/photo.jpg', 'photo');
    $programme = Programme::factory()->create(['image_path' => 'programmes/photo.jpg']);

    $this->actingAs($this->admin)->delete(route('admin.programmes.destroy', $programme))->assertRedirect(route('admin.programmes.index'));

    expect(Programme::count())->toBe(0);
    Storage::disk('public')->assertMissing('programmes/photo.jpg');
});
