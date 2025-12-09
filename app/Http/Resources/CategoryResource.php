<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'type' => $this->type,
            'icon' => $this->getCategoryIcon(),
        ];
    }

    private function getCategoryIcon(): string
    {
        $icons = [
            'Pizzas' => '🍕',
            'Hamburguesas' => '🍔',
            'Ensaladas' => '🥗',
            'Pastas' => '🍝',
            'Pollo' => '🍗',
            'Bebidas' => '🥤',
            'Postres' => '🍰',
            'Entradas' => '🍤',
            'Sopas' => '🍲',
            'Carnes' => '🥩',
        ];

        foreach ($icons as $key => $icon) {
            if (stripos($this->name, $key) !== false) {
                return $icon;
            }
        }

        return '🍽️';
    }
}
