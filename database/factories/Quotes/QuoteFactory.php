<?php

namespace Database\Factories\Quotes;

use App\Models\Companies\Company;
use App\Models\Leads\Lead;
use App\Models\Quotes\Quote;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuoteFactory extends Factory
{
    protected $model = Quote::class;

    public function definition(): array
    {
        return [
            'lead_id' => Lead::factory(),
            'user_id' => User::factory(),
            'company_id' => Company::factory(),
            'base_currency' => fake()->randomElement(['USD', 'MXN']),
            'parent_quote_id' => null,
        ];
    }
}
