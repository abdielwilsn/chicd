<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\StoresPublicUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveProgrammeRequest;
use App\Http\Resources\AdminProgrammeResource;
use App\Models\Programme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProgrammeController extends Controller
{
    use StoresPublicUploads;

    /**
     * Every added programme, drafts included, in website order.
     */
    public function index(): Response
    {
        return Inertia::render('admin/programmes/Index', [
            'programmes' => AdminProgrammeResource::collection(
                Programme::query()->ordered()->get()
            )->resolve(),
        ]);
    }

    /**
     * The form for a new programme.
     */
    public function create(): Response
    {
        return Inertia::render('admin/programmes/Form', [
            'programme' => null,
        ]);
    }

    /**
     * Add a programme.
     */
    public function store(SaveProgrammeRequest $request): RedirectResponse
    {
        $programme = new Programme;

        $this->save($request, $programme);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('":name" was added.', ['name' => $programme->name])]);

        return to_route('admin.programmes.index');
    }

    /**
     * The form for an existing programme.
     */
    public function edit(Programme $programme): Response
    {
        return Inertia::render('admin/programmes/Form', [
            'programme' => AdminProgrammeResource::make($programme)->resolve(),
        ]);
    }

    /**
     * Update a programme, replacing its photograph if a new one was chosen.
     */
    public function update(SaveProgrammeRequest $request, Programme $programme): RedirectResponse
    {
        $this->save($request, $programme);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('":name" was saved.', ['name' => $programme->name])]);

        return to_route('admin.programmes.index');
    }

    /**
     * Remove a programme and its photograph from the website.
     */
    public function destroy(Programme $programme): RedirectResponse
    {
        $programme->delete();
        Storage::disk('public')->delete($programme->image_path);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('":name" was removed from the website.', ['name' => $programme->name])]);

        return to_route('admin.programmes.index');
    }

    /**
     * Fill, store the photograph for, and save a programme.
     */
    private function save(SaveProgrammeRequest $request, Programme $programme): void
    {
        $replacedImage = null;

        $programme->fill([
            'name' => $request->string('name')->toString(),
            'tagline' => $request->filled('tagline') ? $request->string('tagline')->toString() : null,
            'summary' => $request->string('summary')->toString(),
            'body' => $request->filled('body') ? $request->string('body')->toString() : null,
            'image_alt' => $request->filled('image_alt') ? $request->string('image_alt')->toString() : null,
            'sort_order' => $request->integer('sort_order'),
        ]);
        $programme->publishWhen($request->boolean('is_published'));

        if ($image = $request->file('image')) {
            $replacedImage = $programme->image_path;
            $programme->image_path = $this->storeUpload($image, 'programmes');
        }

        $programme->save();

        if ($replacedImage) {
            Storage::disk('public')->delete($replacedImage);
        }
    }
}
