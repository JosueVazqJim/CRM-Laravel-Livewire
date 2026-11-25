<?php

namespace Database\Factories\Leads;

use App\Models\Companies\Company;
use App\Models\Leads\Lead;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'user_id' => User::factory(),
            'billing_id' => null,
            'name_company' => fake()->optional()->company(),
            'state_id' => null,
            'urgency_level' => fake()->randomElement(['baja', 'media', 'alta', 'inmediata']),
            'budget' => fake()->optional()->numberBetween(5000, 500000),
            'undermine_channel' => fake()->optional()->randomElement(['página web', 'meta', 'venta fría']),
            'classification' => fake()->optional()->randomElement(['activo', 'descartado', 'reciclado']),
            'funnel_stage' => fake()->optional()->randomElement(['ventas', 'posventas', 'fidelización', 'reactivación']),
            'client_progress' => fake()->randomElement(['sin contactar', 'nuevo', 'negociación', 'diagnosticado', 'contactado', 'cierre(pendiente adelanto)', 'vendido', 'perdido']),
            'pay_status' => fake()->randomElement(['pago pendiente', 'adelanto', 'liquidado']),
            'after_sales' => fake()->randomElement(['espera de producto', 'pedido preparado', 'pedido enviado', 'pedido entregado']),
            'problem_detected' => fake()->optional()->paragraph(),
            'demo_offered' => fake()->optional()->boolean(),
            'probability' => fake()->numberBetween(0, 100),
            'room_number' => fake()->optional()->numberBetween(1, 50),
            'client_status' => fake()->optional()->randomElement(['en pausa', 'sin respuesta', 'en proceso']),
            'center_number' => fake()->optional()->numberBetween(1, 10),
            'first_contact' => fake()->optional()->boolean(),
            'differentiator_explained' => fake()->optional()->boolean(),
            'diagnosis_made' => fake()->optional()->boolean(),
            'creat_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
