<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\StoresPublicUploads;
use App\Enums\MediaType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveMediaItemRequest;
use App\Http\Resources\AdminMediaItemResource;
use App\Models\MediaItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class MediaItemController extends Controller
{
    use StoresPublicUploads;

    /**
     * Every gallery photograph and video, in gallery order.
     */
    public function index(): Response
    {
        return Inertia::render('admin/gallery/Index', [
            'items' => AdminMediaItemResource::collection(MediaItem::query()->ordered()->get())->resolve(),
        ]);
    }

    /**
     * The form for a new photograph or video.
     */
    public function create(): Response
    {
        return Inertia::render('admin/gallery/Form', [
            'item' => null,
        ]);
    }

    /**
     * Upload a photograph or video to the gallery.
     */
    public function store(SaveMediaItemRequest $request): RedirectResponse
    {
        $file = $request->file('file');

        $item = new MediaItem($this->details($request));
        $item->type = $request->enum('type', MediaType::class);
        $item->file_path = $this->storeUpload($file, 'gallery');
        $item->file_size = $file->getSize();

        if ($item->type === MediaType::Video && $poster = $request->file('poster')) {
            $item->poster_path = $this->storeUpload($poster, 'gallery/posters');
        }

        $item->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('The :type was added to the gallery.', ['type' => strtolower($item->type->label())])]);

        return to_route('admin.gallery.index');
    }

    /**
     * The form for an existing gallery item.
     */
    public function edit(MediaItem $mediaItem): Response
    {
        return Inertia::render('admin/gallery/Form', [
            'item' => AdminMediaItemResource::make($mediaItem)->resolve(),
        ]);
    }

    /**
     * Update a gallery item's caption, description, order or hero setting,
     * and a video's poster image.
     */
    public function update(SaveMediaItemRequest $request, MediaItem $mediaItem): RedirectResponse
    {
        $replacedPoster = null;

        $mediaItem->fill($this->details($request));

        if ($poster = $request->file('poster')) {
            $replacedPoster = $mediaItem->poster_path;
            $mediaItem->poster_path = $this->storeUpload($poster, 'gallery/posters');
        }

        $mediaItem->save();

        if ($replacedPoster) {
            Storage::disk('public')->delete($replacedPoster);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('The :type was saved.', ['type' => strtolower($mediaItem->type->label())])]);

        return to_route('admin.gallery.index');
    }

    /**
     * Remove a photograph or video and its files from the website.
     */
    public function destroy(MediaItem $mediaItem): RedirectResponse
    {
        $mediaItem->delete();
        Storage::disk('public')->delete(array_filter([$mediaItem->file_path, $mediaItem->poster_path]));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('The :type was removed from the gallery.', ['type' => strtolower($mediaItem->type->label())])]);

        return to_route('admin.gallery.index');
    }

    /**
     * The details an admin can change at any time.
     *
     * @return array{caption: string|null, alt_text: string|null, show_in_hero: bool, sort_order: int}
     */
    private function details(SaveMediaItemRequest $request): array
    {
        return [
            'caption' => $request->filled('caption') ? $request->string('caption')->toString() : null,
            'alt_text' => $request->filled('alt_text') ? $request->string('alt_text')->toString() : null,
            'show_in_hero' => $request->boolean('show_in_hero'),
            'sort_order' => $request->integer('sort_order'),
        ];
    }
}
