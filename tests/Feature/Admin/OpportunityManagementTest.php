<?php

use App\Enums\OpportunityType;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->admin = User::factory()->admin()->create();
});

test('staff cannot manage opportunities', function () {
    $this->actingAs(User::factory()->create())->get(route('admin.opportunities.index'))->assertForbidden();
});

test('an admin advertises a consultancy with terms of reference', function () {
    $this->actingAs($this->admin)->post(route('admin.opportunities.store'), [
        'title' => 'Baseline Survey Consultant',
        'type' => 'consultancy',
        'location' => 'Uyo',
        'summary' => 'Lead the SURL baseline survey.',
        'closes_on' => '2026-12-15',
        'application_url' => 'https://forms.example.org/consultant',
        'attachment' => UploadedFile::fake()->create('tor.pdf', 200, 'application/pdf'),
        'is_published' => true,
    ])->assertRedirect(route('admin.opportunities.index'))->assertSessionHasNoErrors();

    $opportunity = Opportunity::sole();
    expect($opportunity->type)->toBe(OpportunityType::Consultancy)
        ->and($opportunity->slug)->toBe('baseline-survey-consultant')
        ->and($opportunity->closes_on->toDateString())->toBe('2026-12-15')
        ->and($opportunity->attachment_size)->toBe(200 * 1024);
    Storage::disk('public')->assertExists($opportunity->attachment_path);
});

test('an application link must be a web address', function () {
    $this->actingAs($this->admin)->post(route('admin.opportunities.store'), [
        'title' => 'Volunteer',
        'type' => 'volunteer',
        'summary' => 'Help out.',
        'application_url' => 'javascript:alert(1)',
    ])->assertSessionHasErrors(['application_url' => 'Enter a full web address, starting with https://.']);
});

test('an admin can remove the attached document', function () {
    Storage::disk('public')->put('opportunities/tor.pdf', 'tor');
    $opportunity = Opportunity::factory()->create(['attachment_path' => 'opportunities/tor.pdf', 'attachment_size' => 3]);

    $this->actingAs($this->admin)->put(route('admin.opportunities.update', $opportunity), [
        'title' => $opportunity->title,
        'type' => $opportunity->type->value,
        'summary' => $opportunity->summary,
        'remove_attachment' => true,
        'is_published' => true,
    ])->assertSessionHasNoErrors();

    expect($opportunity->fresh())->attachment_path->toBeNull()->attachment_size->toBeNull();
    Storage::disk('public')->assertMissing('opportunities/tor.pdf');
});

test('removing an opportunity deletes its document', function () {
    Storage::disk('public')->put('opportunities/tor.pdf', 'tor');
    $opportunity = Opportunity::factory()->create(['attachment_path' => 'opportunities/tor.pdf']);

    $this->actingAs($this->admin)->delete(route('admin.opportunities.destroy', $opportunity))->assertRedirect(route('admin.opportunities.index'));

    expect(Opportunity::count())->toBe(0);
    Storage::disk('public')->assertMissing('opportunities/tor.pdf');
});
