<?php

namespace Database\Factories\Leads;

use App\Models\Leads\BillingAddress;
use Illuminate\Database\Eloquent\Factories\Factory;

class BillingAddressFactory extends Factory
{
    protected $model = BillingAddress::class;

    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['home', 'office', 'fiscal']),
            'state' => fake()->randomElement(['Puebla', 'Ciudad de México', 'Jalisco', 'Nuevo León', 'Querétaro']),
            'municipality' => fake()->randomElement(['Puebla', 'Benito Juárez', 'Guadalajara', 'Monterrey', 'Querétaro']),
            'colony' => fake()->optional()->randomElement(['Centro', 'Lomas', 'Santa María', 'San Pedro', 'Industrial']),
            'street' => fake()->streetName(),
            'exterior_number' => fake()->buildingNumber(),
            'interior_number' => fake()->optional()->buildingNumber(),
            'postal_code' => fake()->numerify('#####'),
        ];
    }
}
