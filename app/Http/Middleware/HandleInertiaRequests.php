<?php

namespace App\Http\Middleware;

use App\Models\Programme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'site' => [
                'name' => config('chicd.name'),
                'shortName' => config('chicd.short_name'),
                'tagline' => config('chicd.tagline'),
                'contact' => config('chicd.contact'),
                'social' => array_filter(config('chicd.social')),
                'programmes' => fn (): array => $this->programmeLinks(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    /**
     * Published added programmes, as links for the Our Work menu.
     *
     * @return list<array{title: string, href: string, description: string|null, image: array{src: string, alt: string}}>
     */
    protected function programmeLinks(): array
    {
        return Programme::query()
            ->published()
            ->ordered()
            ->get(['name', 'slug', 'tagline', 'image_path', 'image_alt'])
            ->map(fn (Programme $programme): array => [
                'title' => $programme->name,
                'href' => route('work.programme', $programme->slug, absolute: false),
                'description' => $programme->tagline,
                'image' => [
                    'src' => Storage::disk('public')->url($programme->image_path),
                    'alt' => $programme->image_alt ?? '',
                ],
            ])
            ->all();
    }
}
