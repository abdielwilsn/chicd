<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Http\Resources\ProgrammeResource;
use App\Models\Post;
use App\Models\Programme;
use Inertia\Inertia;
use Inertia\Response;

class ProgrammeController extends Controller
{
    /**
     * Show Our Work: the thematic areas, the flagship programmes followed by
     * any added programmes, and the latest updates.
     */
    public function index(): Response
    {
        return Inertia::render('site/work/Index', [
            'programmes' => ProgrammeResource::collection(
                Programme::query()->published()->ordered()->get()
            )->resolve(),
            'latestPosts' => PostResource::collection(
                Post::query()->published()->latest('published_at')->limit(3)->get()
            )->resolve(),
        ]);
    }

    /**
     * Show an added programme on the standard programme page.
     */
    public function show(Programme $programme): Response
    {
        abort_unless($programme->isPublished(), 404);

        return Inertia::render('site/work/Programme', [
            'programme' => ProgrammeResource::make($programme)->resolve(),
        ]);
    }
}
