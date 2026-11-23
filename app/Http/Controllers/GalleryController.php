<?php

namespace App\Http\Controllers;

use App\Http\Resources\MediaItemResource;
use App\Models\MediaItem;
use Inertia\Inertia;
use Inertia\Response;

class GalleryController extends Controller
{
    /**
     * Show the Media Gallery of photographs and videos.
     */
    public function __invoke(): Response
    {
        return Inertia::render('site/resources/Gallery', [
            'items' => MediaItemResource::collection(MediaItem::query()->ordered()->get())->resolve(),
        ]);
    }
}
