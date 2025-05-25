<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Post;
use App\Models\Platform;
use Illuminate\Database\Seeder;

class TestDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create();

        // Create platforms individually
        $platforms = [
            Platform::factory()->create([
                'name' => 'Twitter',
                'type' => 'twitter',
                'max_content_length' => 280,
            ]),
            Platform::factory()->create([
                'name' => 'Instagram',
                'type' => 'instagram',
                'max_content_length' => 2200,
            ]),
            Platform::factory()->create([
                'name' => 'LinkedIn',
                'type' => 'linkedin',
                'max_content_length' => 3000,
            ]),
        ];

        $user->platforms()->sync([
            $platforms[0]->id => ['is_active' => true],
            $platforms[1]->id => ['is_active' => false],
            $platforms[2]->id => ['is_active' => false],
        ]);

        Post::factory()->count(5)->create([
            'user_id' => $user->id,
            'status' => 'scheduled',
            'scheduled_time' => now()->subHour(),
        ])->each(function ($post) use ($platforms) {
            $post->platforms()->attach($platforms[0]->id, ['platform_status' => 'pending']);
        });

        Post::factory()->create([
            'user_id' => $user->id,
            'status' => 'published',
            'scheduled_time' => now()->subDay(),
        ]);
    }
}
