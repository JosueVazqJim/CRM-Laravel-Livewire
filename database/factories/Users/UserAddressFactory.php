<?php

namespace Database\Factories\Users;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Users\UserAddress>
 */
class UserAddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['Casa', 'Departamento', 'Provincia', 'Distrito']),
            'state' => fake()->randomElement([1, 2, 3, 4, 5, 6, 7, 8, 9]),
            'municipality' => fake()->randomElement([1, 2, 3, 4, 5, 6, 7, 8, 9]),
            'colony' => fake()->randomElement(['Centro', 'Lomas', 'Santa María', 'San Pedro']),
            'street' => fake()->streetName(),
            'exterior_number' => fake()->buildingNumber(),
            'interior_number' => fake()->optional()->buildingNumber(),
            'postal_code' => fake()->postcode(),
        ];
    }
}
