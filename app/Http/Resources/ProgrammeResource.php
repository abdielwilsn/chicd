<?php

namespace App\Http\Resources;

use App\Concerns\SplitsParagraphs;
use App\Models\Programme;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin Programme
 */
class ProgrammeResource extends JsonResource
{
    use SplitsParagraphs;

    /**
     * Transform the resource into an array.
     *
     * @return array{id: int, name: string, slug: string, tagline: string|null, summary: list<string>, body: list<string>, image: array{src: string, alt: string}, url: string}
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'tagline' => $this->tagline,
            'summary' => $this->paragraphs($this->summary),
            'body' => $this->paragraphs($this->body),
            'image' => [
                'src' => Storage::disk('public')->url($this->image_path),
                'alt' => $this->image_alt ?? '',
            ],
            'url' => route('work.programme', $this->slug, absolute: false),
        ];
    }
}
