<?php

namespace Database\Factories\Leads;

use App\Models\Leads\Lead;
use App\Models\Leads\LeadUserAccess;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadUserAccessFactory extends Factory
{
    protected $model = LeadUserAccess::class;

    public function definition(): array
    {
        return [
            'lead_id' => Lead::factory(),
            'user_id' => User::factory(),
            'permission_level' => fake()->randomElement(['view', 'edit', 'manage']),
        ];
    }
}
