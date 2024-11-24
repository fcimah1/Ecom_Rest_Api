<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
//  * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class AdFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(3),
            'image' => fake()->imageUrl(),
            'status' => fake()->randomElement(['on', 'off']),
            'type' => fake()->randomElement(['Hot Offer ', 'Best Sale', 'New Arrival', 'Featured', 'Sale', 'Home Page', 'Shop Page', 'Checkout Page']),
            'url' => fake()->url(),
            'start_date' => strtotime(fake()->date()),
            'end_date' => strtotime(fake()->date()),
        ];
    }
}
