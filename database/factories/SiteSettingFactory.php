<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SiteSetting>
 */
class SiteSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $segments = ['general', 'auth', 'email', 'security'];
        $types = ['string', 'boolean', 'integer'];

        return [
            'key' => fake()->unique()->slug(2),
            'value' => fake()->word(),
            'segment' => fake()->randomElement($segments),
            'type' => fake()->randomElement($types),
            'description' => fake()->sentence(),
        ];
    }
}
