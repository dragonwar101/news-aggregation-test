<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence;

        return [
            'title' => $title,
            'url' => $this->faker->url,
            'slug' => Str::slug($title),
            'source' => $this->faker->company,
            'original_source' => $this->faker->company,
            'author' => $this->faker->name,
            'image_url' => $this->faker->imageUrl,
            'description' => $this->faker->paragraph,
            'content' => $this->faker->paragraphs(3, true),
            'published_at' => now(),
        ];
    }
}
