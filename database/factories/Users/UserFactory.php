<?php

namespace Database\Factories\Users;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Users\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //            'name' => fake()->name(),
            'first_name' => fake()->firstName(),
            'paternal_surname' => fake()->lastName(),
            'maternal_surname' => fake()->lastName(),
            'sex' => fake()->randomElement(['Masculino', 'Femenino', 'Otro']),
            'rfc' => fake()->unique()->regexify('[A-Z]{4}[0-9]{6}[A-Z0-9]{3}'),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'active' => true,
            'password' => static::$password ??= Hash::make('password'),
            'avatar' => 'https://picsum.photos/200?x='.rand(),
            'remember_token' => Str::random(10),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    public function configure(): static
    {
        return $this->afterCreating(function ($user) {
            $user->assignRole('vendedor');
        });
    }
}
