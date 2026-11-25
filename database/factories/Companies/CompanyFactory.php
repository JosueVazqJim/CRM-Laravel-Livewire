<?php

namespace Database\Factories\Companies;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Companies\Module>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->company();

        return [
            'name' => $name,
            'slug' => strtoupper(fake()->unique()->lexify('???')),
            'avatar' => 'https://picsum.photos/200?x='.rand(),
            'website' => fake()->url(),
            'description' => fake()->sentence(),
            'theme' => $name,
        ];
    }
}
