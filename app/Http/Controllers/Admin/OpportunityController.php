<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\StoresPublicUploads;
use App\Enums\OpportunityType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveOpportunityRequest;
use App\Http\Resources\AdminOpportunityResource;
use App\Models\Opportunity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class OpportunityController extends Controller
{
    use StoresPublicUploads;

    /**
     * Every opportunity, drafts and closed ones included, newest first.
     */
    public function index(): Response
    {
        return Inertia::render('admin/opportunities/Index', [
            'opportunities' => AdminOpportunityResource::collection(
                Opportunity::query()->latest('id')->get()
            )->resolve(),
        ]);
    }

    /**
     * The form for a new opportunity.
     */
    public function create(): Response
    {
        return Inertia::render('admin/opportunities/Form', [
            'opportunity' => null,
            'types' => $this->typeOptions(),
        ]);
    }

    /**
     * Advertise a new opportunity.
     */
    public function store(SaveOpportunityRequest $request): RedirectResponse
    {
        $opportunity = new Opportunity;

        $this->save($request, $opportunity);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('":title" was added.', ['title' => $opportunity->title])]);

        return to_route('admin.opportunities.index');
    }

    /**
     * The form for an existing opportunity.
     */
    public function edit(Opportunity $opportunity): Response
    {
        return Inertia::render('admin/opportunities/Form', [
            'opportunity' => AdminOpportunityResource::make($opportunity)->resolve(),
            'types' => $this->typeOptions(),
        ]);
    }

    /**
     * Update an opportunity, replacing or removing its attached document.
     */
    public function update(SaveOpportunityRequest $request, Opportunity $opportunity): RedirectResponse
    {
        $this->save($request, $opportunity);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('":title" was saved.', ['title' => $opportunity->title])]);

        return to_route('admin.opportunities.index');
    }

    /**
     * Remove an opportunity and its document from the website.
     */
    public function destroy(Opportunity $opportunity): RedirectResponse
    {
        $opportunity->delete();

        if ($opportunity->attachment_path) {
            Storage::disk('public')->delete($opportunity->attachment_path);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('":title" was removed from the website.', ['title' => $opportunity->title])]);

        return to_route('admin.opportunities.index');
    }

    /**
     * Fill, store the document for, and save an opportunity.
     */
    private function save(SaveOpportunityRequest $request, Opportunity $opportunity): void
    {
        $replacedAttachment = null;

        $opportunity->fill([
            'title' => $request->string('title')->toString(),
            'type' => $request->enum('type', OpportunityType::class),
            'location' => $request->filled('location') ? $request->string('location')->toString() : null,
            'summary' => $request->string('summary')->toString(),
            'body' => $request->filled('body') ? $request->string('body')->toString() : null,
            'closes_on' => $request->filled('closes_on') ? $request->string('closes_on')->toString() : null,
            'application_url' => $request->filled('application_url') ? $request->string('application_url')->toString() : null,
        ]);
        $opportunity->publishWhen($request->boolean('is_published'));

        if ($attachment = $request->file('attachment')) {
            $replacedAttachment = $opportunity->attachment_path;
            $opportunity->attachment_path = $this->storeUpload($attachment, 'opportunities');
            $opportunity->attachment_size = $attachment->getSize();
        } elseif ($request->boolean('remove_attachment')) {
            $replacedAttachment = $opportunity->attachment_path;
            $opportunity->attachment_path = null;
            $opportunity->attachment_size = null;
        }

        $opportunity->save();

        if ($replacedAttachment) {
            Storage::disk('public')->delete($replacedAttachment);
        }
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function typeOptions(): array
    {
        return array_map(fn (OpportunityType $type): array => [
            'value' => $type->value,
            'label' => $type->label(),
        ], OpportunityType::cases());
    }
}
