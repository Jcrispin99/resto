<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductTemplate>
 */
class ProductTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => ProductCategory::inRandomOrder()->first()->id ?? ProductCategory::factory(),
            'unit_id' => Unit::inRandomOrder()->first()->id ?? Unit::factory(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'internal_reference' => $this->faker->unique()->bothify('REF-####'),
            'barcode' => $this->faker->ean13(),
            'product_type' => 'storable',
            'can_be_sold' => true,
            'can_be_purchased' => true,
            'can_be_stocked' => true,
            'sale_price' => $this->faker->randomFloat(2, 10, 500),
            'is_active' => true,
        ];
    }
}
