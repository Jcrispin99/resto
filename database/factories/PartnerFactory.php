<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Partner>
 */
class PartnerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isCompany = $this->faker->boolean(70); // 70% companies

        return [
            'code' => $this->faker->unique()->bothify('P-#####'),
            'partner_type' => $isCompany ? 'company' : 'individual',
            'name' => $isCompany ? $this->faker->company() : $this->faker->name(),
            'trade_name' => $isCompany ? $this->faker->companySuffix() : null,
            'tax_id' => $this->faker->unique()->numerify($isCompany ? '20#########1' : '10#######1'), // RUC-like
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'ubigeo_code' => '150101', // Lima default
            'is_customer' => $this->faker->boolean(),
            'is_supplier' => $this->faker->boolean(),
            'payment_terms_days' => $this->faker->randomElement([0, 15, 30, 45, 60]),
            'is_active' => true,
        ];
    }

    public function customer()
    {
        return $this->state(fn (array $attributes) => [
            'is_customer' => true,
            'is_supplier' => false,
        ]);
    }

    public function supplier()
    {
        return $this->state(fn (array $attributes) => [
            'is_customer' => false,
            'is_supplier' => true,
        ]);
    }
}
