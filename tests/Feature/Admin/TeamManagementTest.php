<?php

use App\Enums\TeamSection;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    $this->admin = User::factory()->admin()->create();
});

test('staff cannot manage the team page', function () {
    $this->actingAs(User::factory()->create())->get(route('admin.team.index'))->assertForbidden();
});

test('an admin adds someone to the leadership with a photograph', function () {
    $this->actingAs($this->admin)->post(route('admin.team.store'), [
        'name' => 'Ada Okon',
        'position' => 'Executive Director',
        'section' => 'leadership',
        'bio' => 'Ada leads CHICD.',
        'photo' => UploadedFile::fake()->image('ada.jpg'),
        'is_published' => true,
    ])->assertRedirect(route('admin.team.index'))->assertSessionHasNoErrors();

    $member = TeamMember::sole();
    expect($member->section)->toBe(TeamSection::Leadership)
        ->and($member->isPublished())->toBeTrue();
    Storage::disk('public')->assertExists($member->photo_path);
});

test('a person needs a name, role and section', function () {
    $this->actingAs($this->admin)->post(route('admin.team.store'), [])
        ->assertSessionHasErrors(['name', 'position', 'section']);
});

test('an admin can remove a photograph without replacing it', function () {
    Storage::disk('public')->put('team/photo.jpg', 'photo');
    $member = TeamMember::factory()->create(['photo_path' => 'team/photo.jpg']);

    $this->actingAs($this->admin)->put(route('admin.team.update', $member), [
        'name' => $member->name,
        'position' => $member->position,
        'section' => 'team',
        'remove_photo' => true,
        'is_published' => true,
    ])->assertSessionHasNoErrors();

    expect($member->fresh()->photo_path)->toBeNull();
    Storage::disk('public')->assertMissing('team/photo.jpg');
});

test('removing someone deletes their photograph', function () {
    Storage::disk('public')->put('team/photo.jpg', 'photo');
    $member = TeamMember::factory()->create(['photo_path' => 'team/photo.jpg']);

    $this->actingAs($this->admin)->delete(route('admin.team.destroy', $member))->assertRedirect(route('admin.team.index'));

    expect(TeamMember::count())->toBe(0);
    Storage::disk('public')->assertMissing('team/photo.jpg');
});
