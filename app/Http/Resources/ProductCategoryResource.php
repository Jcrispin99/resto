<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'full_name' => $this->full_name,
            'parent_id' => $this->parent_id,
            'is_active' => $this->is_active,
            'children' => ProductCategoryResource::collection($this->whenLoaded('children')),
            'parent' => new ProductCategoryResource($this->whenLoaded('parent')),
        ];
    }
}
