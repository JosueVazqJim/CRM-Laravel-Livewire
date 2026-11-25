<?php

namespace Database\Factories\Leads;

use App\Models\Leads\BillingAddress;
use App\Models\Leads\BillingInfo;
use Illuminate\Database\Eloquent\Factories\Factory;

class BillingInfoFactory extends Factory
{
    protected $model = BillingInfo::class;

    public function definition(): array
    {
        return [
            'business_name' => fake()->company(),
            'rfc' => fake()->unique()->regexify('[A-Z]{4}[0-9]{6}[A-Z0-9]{3}'),
            'email' => fake()->optional()->safeEmail(),
            'address_id' => BillingAddress::factory(),
        ];
    }
}
