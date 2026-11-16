<?php

namespace App\Http\Resources;

use App\Models\Publication;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Number;

/**
 * @mixin Publication
 */
class PublicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{id: int, title: string, category: string, category_label: string, year: int, summary: string|null, file_url: string, file_size: string|null, cover_image_url: string|null}
     */
    public function toArray(Request $request): array
    {
        $disk = Storage::disk('public');

        return [
            'id' => $this->id,
            'title' => $this->title,
            'category' => $this->category->value,
            'category_label' => $this->category->label(),
            'year' => $this->year,
            'summary' => $this->summary,
            'file_url' => $disk->url($this->file_path),
            'file_size' => $this->file_size ? Number::fileSize($this->file_size, precision: 1) : null,
            'cover_image_url' => $this->cover_image_path ? $disk->url($this->cover_image_path) : null,
        ];
    }
}
