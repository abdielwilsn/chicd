<?php

use App\Enums\PublicationCategory;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('public');
    $this->admin = User::factory()->admin()->create();
});

test('staff cannot manage publications', function () {
    $this->actingAs(User::factory()->create())->get(route('admin.publications.index'))->assertForbidden();
});

test('admins see every publication, drafts included', function () {
    $draft = Publication::factory()->unpublished()->create(['year' => 2027]);
    $published = Publication::factory()->create(['year' => 2026]);

    $this->actingAs($this->admin)->get(route('admin.publications.index'))->assertInertia(fn (Assert $page) => $page
        ->component('admin/publications/Index')
        ->where('publications.0.id', $draft->id)
        ->where('publications.0.is_published', false)
        ->where('publications.1.id', $published->id)
        ->where('publications.1.is_published', true)
    );
});

test('an admin uploads and publishes a document', function () {
    $this->travelTo('2026-11-20 10:00:00');

    $this->actingAs($this->admin)->post(route('admin.publications.store'), [
        'title' => 'Safeguarding Policy',
        'category' => 'governance',
        'year' => 2026,
        'summary' => 'How CHICD keeps people safe.',
        'file' => UploadedFile::fake()->create('safeguarding.pdf', 300, 'application/pdf'),
        'cover_image' => UploadedFile::fake()->image('cover.jpg'),
        'is_published' => true,
    ])->assertRedirect(route('admin.publications.index'))->assertSessionHasNoErrors();

    $publication = Publication::sole();
    expect($publication->category)->toBe(PublicationCategory::Governance)
        ->and($publication->published_at->toDateTimeString())->toBe('2026-11-20 10:00:00')
        ->and($publication->file_size)->toBe(300 * 1024);
    Storage::disk('public')->assertExists([$publication->file_path, $publication->cover_image_path]);
});

test('a new publication needs a document of an allowed type', function () {
    $this->actingAs($this->admin)->post(route('admin.publications.store'), [
        'title' => 'Report',
        'category' => 'research',
        'year' => 2026,
    ])->assertSessionHasErrors(['file' => 'Please choose the document to upload.']);

    $this->actingAs($this->admin)->post(route('admin.publications.store'), [
        'title' => 'Report',
        'category' => 'research',
        'year' => 2026,
        'file' => UploadedFile::fake()->create('script.exe', 10),
    ])->assertSessionHasErrors('file');

    expect(Publication::count())->toBe(0);
});

test('saving a draft keeps it off the website', function () {
    $this->actingAs($this->admin)->post(route('admin.publications.store'), [
        'title' => 'Draft toolkit',
        'category' => 'toolkit',
        'year' => 2026,
        'file' => UploadedFile::fake()->create('toolkit.pdf', 100, 'application/pdf'),
        'is_published' => false,
    ])->assertSessionHasNoErrors();

    expect(Publication::sole()->published_at)->toBeNull();
});

test('replacing a document removes the old file, and editing keeps the original publish date', function () {
    $this->travelTo('2026-11-20 10:00:00');
    Storage::disk('public')->put('publications/old.pdf', 'old');
    $publication = Publication::factory()->create(['file_path' => 'publications/old.pdf', 'published_at' => '2026-11-01 09:00:00']);

    $this->actingAs($this->admin)->put(route('admin.publications.update', $publication), [
        'title' => 'Renamed',
        'category' => $publication->category->value,
        'year' => $publication->year,
        'file' => UploadedFile::fake()->create('new.pdf', 50, 'application/pdf'),
        'is_published' => true,
    ])->assertSessionHasNoErrors();

    $publication->refresh();
    expect($publication->title)->toBe('Renamed')
        ->and($publication->published_at->toDateTimeString())->toBe('2026-11-01 09:00:00');
    Storage::disk('public')->assertMissing('publications/old.pdf');
    Storage::disk('public')->assertExists($publication->file_path);
});

test('featuring an annual report un-features the previous one', function () {
    $previous = Publication::factory()->annualReport()->featured()->create();
    $report = Publication::factory()->annualReport()->create();

    $this->actingAs($this->admin)->put(route('admin.publications.update', $report), [
        'title' => $report->title,
        'category' => 'annual_report',
        'year' => $report->year,
        'is_published' => true,
        'is_featured' => true,
    ])->assertSessionHasNoErrors();

    expect($report->fresh()->is_featured)->toBeTrue()
        ->and($previous->fresh()->is_featured)->toBeFalse();
});

test('only annual reports can be featured', function () {
    $research = Publication::factory()->create(['category' => PublicationCategory::Research]);

    $this->actingAs($this->admin)->put(route('admin.publications.update', $research), [
        'title' => $research->title,
        'category' => 'research',
        'year' => $research->year,
        'is_published' => true,
        'is_featured' => true,
    ]);

    expect($research->fresh()->is_featured)->toBeFalse();
});

test('removing a publication deletes its files', function () {
    Storage::disk('public')->put('publications/doc.pdf', 'doc');
    $publication = Publication::factory()->create(['file_path' => 'publications/doc.pdf']);

    $this->actingAs($this->admin)->delete(route('admin.publications.destroy', $publication))->assertRedirect(route('admin.publications.index'));

    expect(Publication::count())->toBe(0);
    Storage::disk('public')->assertMissing('publications/doc.pdf');
});
