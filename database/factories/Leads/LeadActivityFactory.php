<?php

namespace Database\Factories\Leads;

use App\Models\Leads\Lead;
use App\Models\Leads\LeadActivity;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadActivityFactory extends Factory
{
    protected $model = LeadActivity::class;

    public function definition(): array
    {
        $isCompleted = fake()->boolean(70);

        // Si está completada, scheduled_at debe ser pasado o nulo
        $scheduledAt = $isCompleted
            ? fake()->optional(0.5)->dateTimeBetween('-2 months', 'now')
            : fake()->optional(0.7)->dateTimeBetween('-2 months', '+1 month');

        $completedAt = null;
        if ($isCompleted) {
            $completedAt = $scheduledAt
                ? fake()->dateTimeBetween($scheduledAt, 'now')
                : fake()->dateTimeBetween('-3 months', 'now');
        }

        return [
            'lead_id' => Lead::factory(),

            'date_action' => fake()->dateTimeBetween('-3 months', 'now'),

            'type_action' => fake()->optional(0.8)->randomElement([
                'llamada',
                'email',
                'reunión',
                'visita',
                'demo',
                'seguimiento',
            ]),

            'is_completed' => $isCompleted,

            'scheduled_at' => $scheduledAt,

            'completed_at' => $completedAt,

            'created_at' => fake()->optional(0.5)->dateTimeBetween('-3 months', 'now'),
        ];
    }
}
