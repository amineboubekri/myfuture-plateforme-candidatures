<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'role' => 'student',
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'date_of_birth' => fake()->dateTimeBetween('-30 years', '-18 years'),
            'is_approved' => fake()->boolean(80), // 80% chance of being approved
            'profile_completed' => fake()->boolean(70), // 70% chance of having complete profile
            'google2fa_secret' => null,
            'google2fa_enabled' => false,
            'google2fa_enabled_at' => null,
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
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'is_approved' => true,
            'profile_completed' => true,
        ]);
    }

    /**
     * Indicate that the user is a student.
     */
    public function student(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'student',
        ]);
    }

    /**
     * Indicate that the user is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => true,
        ]);
    }

    /**
     * Indicate that the user is not approved.
     */
    public function unapproved(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => false,
        ]);
    }

    /**
     * Indicate that the user has a complete profile.
     */
    public function profileComplete(): static
    {
        return $this->state(fn (array $attributes) => [
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'date_of_birth' => fake()->dateTimeBetween('-30 years', '-18 years'),
            'profile_completed' => true,
        ]);
    }

    /**
     * Indicate that the user has an incomplete profile.
     */
    public function profileIncomplete(): static
    {
        return $this->state(fn (array $attributes) => [
            'phone' => null,
            'address' => null,
            'date_of_birth' => null,
            'profile_completed' => false,
        ]);
    }

    /**
     * Indicate that the user has 2FA enabled.
     */
    public function with2FA(): static
    {
        return $this->state(fn (array $attributes) => [
            'google2fa_secret' => fake()->regexify('[A-Z0-9]{32}'),
            'google2fa_enabled' => true,
            'google2fa_enabled_at' => now(),
        ]);
    }

    /**
     * Indicate that the user has 2FA disabled.
     */
    public function without2FA(): static
    {
        return $this->state(fn (array $attributes) => [
            'google2fa_secret' => null,
            'google2fa_enabled' => false,
            'google2fa_enabled_at' => null,
        ]);
    }

    /**
     * Indicate that the user has verified email.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Indicate that the user has unverified email.
     */
    public function emailUnverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
