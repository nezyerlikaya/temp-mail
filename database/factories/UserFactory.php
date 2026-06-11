<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
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
            'name' => fake()->name(),
            'display_name' => null,
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'status' => 'active',
            'role' => 'member',
            'timezone' => 'UTC',
            'language_preference' => 'en',
            'bio' => null,
            'website' => null,
            'avatar_media_id' => null,
            'public_author_slug' => null,
            'author_bio' => null,
            'social_links' => null,
            'author_profile_active' => false,
            'featured_author' => false,
            'avatar_color' => '#0f766e',
            'current_plan_reference' => null,
            'membership_status' => 'none',
            'premium_starts_at' => null,
            'premium_ends_at' => null,
            'membership_granted_by' => null,
            'password' => static::$password ??= Hash::make('password'),
            'is_admin' => false,
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

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_admin' => true,
            'role' => 'admin',
        ]);
    }

    public function owner(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_admin' => true,
            'role' => 'owner',
        ]);
    }

    public function editor(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_admin' => true,
            'role' => 'editor',
        ]);
    }

    public function author(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_admin' => true,
            'role' => 'author',
            'display_name' => fake()->name(),
            'public_author_slug' => fake()->unique()->slug(2),
            'author_bio' => fake()->paragraph(),
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'suspended',
        ]);
    }
}
