<?php

namespace App\Http\Controllers;

use App\Http\Resources\MediaItemResource;
use App\Http\Resources\ProgrammeResource;
use App\Models\MediaItem;
use App\Models\Programme;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Show the home page with the gallery items chosen for the hero and any
     * programmes added after the flagship ones.
     */
    public function __invoke(): Response
    {
        return Inertia::render('site/Home', [
            'heroMedia' => MediaItemResource::collection(
                MediaItem::query()->where('show_in_hero', true)->ordered()->get()
            )->resolve(),
            'programmes' => ProgrammeResource::collection(
                Programme::query()->published()->ordered()->get()
            )->resolve(),
        ]);
    }
}
