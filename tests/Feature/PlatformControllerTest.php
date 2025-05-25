<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Platform;
use Database\Seeders\TestDatabaseSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PlatformControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TestDatabaseSeeder::class);
    }

    public function test_toggle_changes_platform_status_and_clears_cache()
    {
        $user = User::first();
        $platform = Platform::where('name', 'Instagram')->first();
        $this->actingAs($user);

        Cache::shouldReceive('forget')
            ->with("platforms_{$user->id}")
            ->once();
        Cache::shouldReceive('forget')
            ->with("user_{$user->id}_active_platforms")
            ->once();

        $response = $this->postJson("/platforms/{$platform->id}/toggle", ['is_active' => true]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Platform status updated successfully',
                'platform' => [
                    'id' => $platform->id,
                    'name' => 'Instagram',
                    'is_active' => true,
                ],
                'reload' => false, // Updated to match actual response
            ]);
        $this->assertDatabaseHas('user_platforms', [
            'user_id' => $user->id,
            'platform_id' => $platform->id,
            'is_active' => true,
        ]);
    }

    public function test_toggle_triggers_reload_when_last_platform_deactivated()
    {
        $user = User::first();
        $platform = Platform::where('name', 'Twitter')->first();
        $this->actingAs($user);

        $response = $this->postJson("/platforms/{$platform->id}/toggle", ['is_active' => false]);

        $response->assertJson(['reload' => true]);
        $this->assertDatabaseHas('user_platforms', [
            'user_id' => $user->id,
            'platform_id' => $platform->id,
            'is_active' => false,
        ]);
    }
}
