<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'slug'         => $this->slug,
            'description'  => $this->description,
            'price'        => $this->price,
            'currency'     => $this->currency,
            'color'        => $this->color,
            'reference'    => $this->reference,
            'status'       => $this->status,
            'published_at' => $this->published_at?->toIso8601String(),
            'category'     => $this->whenLoaded('category', fn () => [
                'id'   => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'images'       => $this->whenLoaded('images', fn () => $this->images->map(fn ($img) => [
                'id'         => $img->id,
                'path'       => $img->path,
                'type'       => $img->type,
                'alt_text'   => $img->alt_text,
                'sort_order' => $img->sort_order,
            ])),
            'created_at'   => $this->created_at->toIso8601String(),
            'updated_at'   => $this->updated_at->toIso8601String(),
        ];
    }
}
