<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
            'image_url' => $this->faker->imageUrl(),
            'scheduled_time' => now(),
            'status' => 'scheduled',
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
