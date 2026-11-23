<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\StoresPublicUploads;
use App\Enums\TeamSection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveTeamMemberRequest;
use App\Http\Resources\AdminTeamMemberResource;
use App\Models\TeamMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class TeamMemberController extends Controller
{
    use StoresPublicUploads;

    /**
     * Everyone on the Leadership & Team page, hidden people included, in
     * website order.
     */
    public function index(): Response
    {
        return Inertia::render('admin/team/Index', [
            'members' => AdminTeamMemberResource::collection(
                TeamMember::query()->orderBy('section')->ordered()->get()
            )->resolve(),
        ]);
    }

    /**
     * The form for a new team member.
     */
    public function create(): Response
    {
        return Inertia::render('admin/team/Form', [
            'member' => null,
            'sections' => $this->sectionOptions(),
        ]);
    }

    /**
     * Add someone to the Leadership & Team page.
     */
    public function store(SaveTeamMemberRequest $request): RedirectResponse
    {
        $member = new TeamMember;

        $this->save($request, $member);

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':name was added to the team page.', ['name' => $member->name])]);

        return to_route('admin.team.index');
    }

    /**
     * The form for an existing team member.
     */
    public function edit(TeamMember $teamMember): Response
    {
        return Inertia::render('admin/team/Form', [
            'member' => AdminTeamMemberResource::make($teamMember)->resolve(),
            'sections' => $this->sectionOptions(),
        ]);
    }

    /**
     * Update a team member, replacing or removing their photograph.
     */
    public function update(SaveTeamMemberRequest $request, TeamMember $teamMember): RedirectResponse
    {
        $this->save($request, $teamMember);

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':name was saved.', ['name' => $teamMember->name])]);

        return to_route('admin.team.index');
    }

    /**
     * Remove someone and their photograph from the team page.
     */
    public function destroy(TeamMember $teamMember): RedirectResponse
    {
        $teamMember->delete();

        if ($teamMember->photo_path) {
            Storage::disk('public')->delete($teamMember->photo_path);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __(':name was removed from the team page.', ['name' => $teamMember->name])]);

        return to_route('admin.team.index');
    }

    /**
     * Fill, store the photograph for, and save a team member.
     */
    private function save(SaveTeamMemberRequest $request, TeamMember $member): void
    {
        $replacedPhoto = null;

        $member->fill([
            'name' => $request->string('name')->toString(),
            'position' => $request->string('position')->toString(),
            'section' => $request->enum('section', TeamSection::class),
            'bio' => $request->filled('bio') ? $request->string('bio')->toString() : null,
            'sort_order' => $request->integer('sort_order'),
        ]);
        $member->publishWhen($request->boolean('is_published'));

        if ($photo = $request->file('photo')) {
            $replacedPhoto = $member->photo_path;
            $member->photo_path = $this->storeUpload($photo, 'team');
        } elseif ($request->boolean('remove_photo')) {
            $replacedPhoto = $member->photo_path;
            $member->photo_path = null;
        }

        $member->save();

        if ($replacedPhoto) {
            Storage::disk('public')->delete($replacedPhoto);
        }
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function sectionOptions(): array
    {
        return array_map(fn (TeamSection $section): array => [
            'value' => $section->value,
            'label' => $section->label(),
        ], TeamSection::cases());
    }
}
