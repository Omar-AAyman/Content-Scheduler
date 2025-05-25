<?php

namespace Database\Factories;

use App\Models\Platform;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlatformFactory extends Factory
{
    protected $model = Platform::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'type' => $this->faker->word,
            'max_content_length' => $this->faker->numberBetween(280, 3000),
        ];
    }
}
