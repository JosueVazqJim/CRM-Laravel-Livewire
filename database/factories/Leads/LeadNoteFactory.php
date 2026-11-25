<?php

namespace Database\Factories\Leads;

use App\Models\Leads\Lead;
use App\Models\Leads\LeadNote;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadNoteFactory extends Factory
{
    protected $model = LeadNote::class;

    public function definition(): array
    {
        return [
            'lead_id' => Lead::factory(),
            'user_id' => User::factory(),
            'content' => fake()->paragraph(),
        ];
    }
}
