<?php

namespace Database\Factories;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Upload;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Blog>
 */
class BlogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        
        return [
            'category_id' => BlogCategory::all()->random()->id, // Randomly selects a category ID
            'title' => fake()->sentence(3),
            'slug' => fake()->slug(3),
            'short_description' => fake()->sentence(3),
            'description' => fake()->sentence(3),
            'banner' => Upload::all()->random()->id,
            'meta_title' => Upload::all()->random()->id,
            'meta_img' => Upload::all()->random()->id,
            'meta_description' => fake()->sentence(3),
            'meta_keywords' => fake()->sentence(3),
            'status' => fake()->randomElement([0, 1]),
        ];
    }
}
