<?php

namespace App\Http\Resources;

use App\Models\MediaItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin MediaItem
 */
class MediaItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{id: int, type: string, caption: string|null, alt: string, url: string, poster_url: string|null}
     */
    public function toArray(Request $request): array
    {
        $disk = Storage::disk('public');

        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'caption' => $this->caption,
            'alt' => $this->alt_text ?? '',
            'url' => $disk->url($this->file_path),
            'poster_url' => $this->poster_path ? $disk->url($this->poster_path) : null,
        ];
    }
}
