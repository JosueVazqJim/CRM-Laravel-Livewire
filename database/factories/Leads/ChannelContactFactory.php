<?php

namespace Database\Factories\Leads;

use App\Models\Leads\ChannelContact;
use App\Models\Leads\ContactPerson;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChannelContactFactory extends Factory
{
    protected $model = ChannelContact::class;

    public function definition(): array
    {
        return [
            'lead_contact_id' => ContactPerson::factory(),
            'type' => fake()->randomElement(['teléfono', 'email', 'whatsapp', 'red social']),
            'channel' => fake()->randomElement([
                fake()->phoneNumber(),
                fake()->safeEmail(),
                'https://wa.me/52'.fake()->numerify('##########'),
                'https://facebook.com/'.fake()->userName(),
            ]),
        ];
    }
}
