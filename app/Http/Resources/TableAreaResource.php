<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TableAreaResource extends JsonResource
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
            'description' => $this->description,
            'is_active' => (bool) $this->is_active,
            'branch' => $this->when($this->branch, [
                'id' => $this->branch?->id,
                'name' => $this->branch?->name,
            ]),
        ];
    }
}
