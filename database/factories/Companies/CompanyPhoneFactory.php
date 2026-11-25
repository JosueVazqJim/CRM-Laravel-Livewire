<?php

namespace Database\Factories\Companies;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Companies\CompanyPhone>
 */
class CompanyPhoneFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'phone' => fake()->phoneNumber(),
        ];
    }
}
