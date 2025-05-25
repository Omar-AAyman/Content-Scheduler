<?php

namespace Database\Seeders;

use App\Models\Platform;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;

class PlatformSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get default platforms from config or use hardcoded defaults if config not available
        $defaultPlatforms = Config::get('content_scheduler.default_platforms', [
            [
                'name' => 'Twitter',
                'type' => 'twitter',
                'max_content_length' => 280,
            ],
            [
                'name' => 'Instagram',
                'type' => 'instagram',
                'max_content_length' => 2200,
            ],
            [
                'name' => 'LinkedIn',
                'type' => 'linkedin',
                'max_content_length' => 3000,
            ],
        ]);

        foreach ($defaultPlatforms as $platform) {
            Platform::updateOrCreate(
                ['type' => $platform['type']],
                [
                    'name' => $platform['name'],
                    'max_content_length' => $platform['max_content_length'],
                ]
            );
        }

        $this->command->info('Default platforms seeded successfully!');
    }
}
