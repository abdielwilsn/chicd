<?php

namespace App\Http\Controllers;

use App\Enums\PostType;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    /**
     * Show News & Stories: upcoming events and the latest posts, optionally
     * of one type, twelve at a time.
     */
    public function index(Request $request): Response
    {
        $type = $request->enum('type', PostType::class);

        $posts = Post::query()
            ->published()
            ->when($type, fn (Builder $query) => $query->where('type', $type))
            ->latest('published_at')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('site/resources/News', [
            'posts' => PostResource::collection($posts->getCollection())->resolve(),
            'pagination' => [
                'currentPage' => $posts->currentPage(),
                'lastPage' => $posts->lastPage(),
                'previousUrl' => $posts->previousPageUrl(),
                'nextUrl' => $posts->nextPageUrl(),
            ],
            'upcomingEvents' => $type === null || $type === PostType::Event
                ? PostResource::collection(
                    Post::query()->published()->upcomingEvents()->orderBy('event_starts_at')->limit(3)->get()
                )->resolve()
                : [],
            'filters' => ['type' => $type?->value],
            'typeOptions' => array_map(fn (PostType $option): array => [
                'value' => $option->value,
                'label' => $option->pluralLabel(),
            ], PostType::cases()),
        ]);
    }

    /**
     * Show one post, with a few more to read next.
     */
    public function show(Post $post): Response
    {
        abort_unless($post->isPublished(), 404);

        return Inertia::render('site/resources/Post', [
            'post' => PostResource::make($post)->resolve(),
            'morePosts' => PostResource::collection(
                Post::query()->published()->whereKeyNot($post->id)->latest('published_at')->limit(3)->get()
            )->resolve(),
        ]);
    }
}
