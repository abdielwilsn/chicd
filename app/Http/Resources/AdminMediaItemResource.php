<?php

namespace App\Http\Resources;

use App\Models\MediaItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Number;

/**
 * A gallery item as shown in the admin area.
 *
 * @mixin MediaItem
 */
class AdminMediaItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...MediaItemResource::make($this->resource)->resolve(),
            'type_label' => $this->type->label(),
            'alt_text' => $this->alt_text,
            'file_size' => $this->file_size ? Number::fileSize($this->file_size, precision: 1) : null,
            'show_in_hero' => $this->show_in_hero,
            'sort_order' => $this->sort_order,
        ];
    }
}
