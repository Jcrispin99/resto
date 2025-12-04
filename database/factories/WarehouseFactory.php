<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Warehouse>
 */
class WarehouseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'branch_id' => Company::inRandomOrder()->first()->id ?? Company::factory(),
            'code' => $this->faker->unique()->bothify('WH-###'),
            'name' => $this->faker->city() . ' Warehouse',
            'is_active' => true,
        ];
    }
}
