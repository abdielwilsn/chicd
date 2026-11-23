<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\StoresPublicUploads;
use App\Enums\PublicationCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SavePublicationRequest;
use App\Http\Resources\AdminPublicationResource;
use App\Models\Publication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PublicationController extends Controller
{
    use StoresPublicUploads;

    /**
     * Every publication, drafts included, newest first.
     */
    public function index(): Response
    {
        return Inertia::render('admin/publications/Index', [
            'publications' => AdminPublicationResource::collection(
                Publication::query()->latest('year')->latest('id')->get()
            )->resolve(),
        ]);
    }

    /**
     * The form for a new publication.
     */
    public function create(): Response
    {
        return Inertia::render('admin/publications/Form', [
            'publication' => null,
            'categories' => $this->categoryOptions(),
        ]);
    }

    /**
     * Upload a new publication.
     */
    public function store(SavePublicationRequest $request): RedirectResponse
    {
        $publication = new Publication;

        $this->save($request, $publication);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('":title" was added.', ['title' => $publication->title])]);

        return to_route('admin.publications.index');
    }

    /**
     * The form for an existing publication.
     */
    public function edit(Publication $publication): Response
    {
        return Inertia::render('admin/publications/Form', [
            'publication' => AdminPublicationResource::make($publication)->resolve(),
            'categories' => $this->categoryOptions(),
        ]);
    }

    /**
     * Update a publication, replacing its document or cover if new ones were chosen.
     */
    public function update(SavePublicationRequest $request, Publication $publication): RedirectResponse
    {
        $this->save($request, $publication);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('":title" was saved.', ['title' => $publication->title])]);

        return to_route('admin.publications.index');
    }

    /**
     * Remove a publication and its files from the website.
     */
    public function destroy(Publication $publication): RedirectResponse
    {
        $files = array_filter([$publication->file_path, $publication->cover_image_path]);

        $publication->delete();
        Storage::disk('public')->delete($files);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('":title" was removed from the website.', ['title' => $publication->title])]);

        return to_route('admin.publications.index');
    }

    /**
     * Fill, store files for, and save a publication. Featuring an annual report
     * stops any other annual report being featured.
     */
    private function save(SavePublicationRequest $request, Publication $publication): void
    {
        $disk = Storage::disk('public');
        $replacedFiles = [];

        $publication->fill([
            'title' => $request->string('title')->toString(),
            'category' => $request->enum('category', PublicationCategory::class),
            'year' => $request->integer('year'),
            'summary' => $request->filled('summary') ? $request->string('summary')->toString() : null,
            'is_featured' => $request->boolean('is_featured') && $request->enum('category', PublicationCategory::class) === PublicationCategory::AnnualReport,
        ]);
        $publication->publishWhen($request->boolean('is_published'));

        if ($file = $request->file('file')) {
            $replacedFiles[] = $publication->file_path;
            $publication->file_path = $this->storeUpload($file, 'publications');
            $publication->file_size = $file->getSize();
        }

        if ($cover = $request->file('cover_image')) {
            $replacedFiles[] = $publication->cover_image_path;
            $publication->cover_image_path = $this->storeUpload($cover, 'publications/covers');
        } elseif ($request->boolean('remove_cover_image')) {
            $replacedFiles[] = $publication->cover_image_path;
            $publication->cover_image_path = null;
        }

        DB::transaction(function () use ($publication): void {
            $publication->save();

            if ($publication->is_featured) {
                Publication::query()
                    ->where('category', PublicationCategory::AnnualReport)
                    ->whereKeyNot($publication->id)
                    ->update(['is_featured' => false]);
            }
        });

        $disk->delete(array_values(array_filter($replacedFiles)));
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function categoryOptions(): array
    {
        return array_map(fn (PublicationCategory $category): array => [
            'value' => $category->value,
            'label' => $category->label(),
        ], PublicationCategory::cases());
    }
}
