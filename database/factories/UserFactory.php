<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    /**
     * Roles are reference data a seeder puts in, and RefreshDatabase takes it
     * back out, so the factory stands one up if the table is empty. Inserted
     * straight through the query builder: the table carries no timestamps.
     */
    private static function ensureUserRole(): int
    {
        \Illuminate\Support\Facades\DB::table('roles')->insertOrIgnore([
            'id' => \App\Models\Role::USER_ROLE_ID,
            'role' => 'User',
            'role_slug' => 'user',
        ]);

        return \App\Models\Role::USER_ROLE_ID;
    }

    public function definition(): array
    {
        return [
            // The stock factory still asked for a `name` column; this
            // project's users have been first_name / last_name since the start.
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'phone' => fake()->numerify('+38(0##)###-##-##'),
            // Roles are reference data a seeder puts in, and RefreshDatabase
            // takes it back out — so the factory stands one up if it has to.
            'role_id' => self::ensureUserRole(),
            'language' => 'uk',
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
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
}
