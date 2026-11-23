<?php

namespace App\Http\Controllers\Admin;

use App\Concerns\StoresPublicUploads;
use App\Enums\PostType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SavePostRequest;
use App\Http\Resources\AdminPostResource;
use App\Models\Post;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    use StoresPublicUploads;

    /**
     * Every story, news article, event and announcement, drafts included,
     * newest first.
     */
    public function index(): Response
    {
        return Inertia::render('admin/news/Index', [
            'posts' => AdminPostResource::collection(
                Post::query()->latest('id')->get()
            )->resolve(),
        ]);
    }

    /**
     * The form for a new post.
     */
    public function create(): Response
    {
        return Inertia::render('admin/news/Form', [
            'post' => null,
            'types' => $this->typeOptions(),
        ]);
    }

    /**
     * Publish a new post.
     */
    public function store(SavePostRequest $request): RedirectResponse
    {
        $post = new Post;

        $this->save($request, $post);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('":title" was added.', ['title' => $post->title])]);

        return to_route('admin.news.index');
    }

    /**
     * The form for an existing post.
     */
    public function edit(Post $post): Response
    {
        return Inertia::render('admin/news/Form', [
            'post' => AdminPostResource::make($post)->resolve(),
            'types' => $this->typeOptions(),
        ]);
    }

    /**
     * Update a post, replacing or removing its cover image.
     */
    public function update(SavePostRequest $request, Post $post): RedirectResponse
    {
        $this->save($request, $post);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('":title" was saved.', ['title' => $post->title])]);

        return to_route('admin.news.index');
    }

    /**
     * Remove a post and its cover image from the website.
     */
    public function destroy(Post $post): RedirectResponse
    {
        $post->delete();

        if ($post->cover_image_path) {
            Storage::disk('public')->delete($post->cover_image_path);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('":title" was removed from the website.', ['title' => $post->title])]);

        return to_route('admin.news.index');
    }

    /**
     * Fill, store the cover image for, and save a post. Event details are
     * only kept for events; the start time is entered in the organisation
     * timezone.
     */
    private function save(SavePostRequest $request, Post $post): void
    {
        $type = $request->enum('type', PostType::class);
        $isEvent = $type === PostType::Event;
        $replacedCover = null;

        $post->fill([
            'title' => $request->string('title')->toString(),
            'type' => $type,
            'excerpt' => $request->filled('excerpt') ? $request->string('excerpt')->toString() : null,
            'body' => $request->string('body')->toString(),
            'cover_image_alt' => $request->filled('cover_image_alt') ? $request->string('cover_image_alt')->toString() : null,
            'event_starts_at' => $isEvent
                ? CarbonImmutable::createFromFormat('Y-m-d\TH:i', $request->string('event_starts_at')->toString(), config('chicd.timezone'))
                    ->setTimezone(config('app.timezone'))
                : null,
            'event_location' => $isEvent && $request->filled('event_location') ? $request->string('event_location')->toString() : null,
        ]);
        $post->publishWhen($request->boolean('is_published'));

        if ($cover = $request->file('cover_image')) {
            $replacedCover = $post->cover_image_path;
            $post->cover_image_path = $this->storeUpload($cover, 'news');
        } elseif ($request->boolean('remove_cover_image')) {
            $replacedCover = $post->cover_image_path;
            $post->cover_image_path = null;
        }

        $post->save();

        if ($replacedCover) {
            Storage::disk('public')->delete($replacedCover);
        }
    }

    /**
     * @return list<array{value: string, label: string}>
     */
    private function typeOptions(): array
    {
        return array_map(fn (PostType $type): array => [
            'value' => $type->value,
            'label' => $type->label(),
        ], PostType::cases());
    }
}
