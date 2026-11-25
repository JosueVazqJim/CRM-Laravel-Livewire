<?php

namespace Database\Factories\Leads;

use App\Models\Leads\ContactPerson;
use App\Models\Leads\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactPersonFactory extends Factory
{
    protected $model = ContactPerson::class;

    public function definition(): array
    {
        return [
            'lead_id' => Lead::factory(),
            'name_contact' => fake()->name(),
            'recurring_client' => fake()->boolean(),
            'creat_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
