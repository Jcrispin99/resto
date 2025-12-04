<?php

namespace Database\Factories;

use App\Models\ProductTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductProduct>
 */
class ProductProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'template_id' => ProductTemplate::factory(),
            'sku' => $this->faker->unique()->bothify('SKU-#####'),
            'barcode' => $this->faker->unique()->ean13(),
            'sale_price' => null, // Use template price by default
            'is_active' => true,
        ];
    }
}
