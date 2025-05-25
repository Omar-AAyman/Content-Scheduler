<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\Platform;
use Database\Seeders\TestDatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TestDatabaseSeeder::class);
    }

    public function test_index_displays_filtered_posts_and_stats()
    {
        $user = User::first();
        $this->actingAs($user);

        $response = $this->get('/dashboard?status=scheduled');

        $response->assertStatus(200)
            ->assertViewIs('pages.dashboard')
            ->assertViewHas('posts', function ($posts) {
                return $posts->every(fn($post) => $post->status === 'scheduled');
            })
            ->assertViewHas('hasActivePlatforms', true)
            ->assertViewHas('platformStats', function ($stats) {
                return $stats->isNotEmpty() && $stats->every(fn($stat) => isset($stat->name, $stat->count, $stat->percentage));
            });
    }

    public function test_store_creates_post_with_platforms()
    {
        $user = User::first();
        $platform = Platform::where('name', 'Twitter')->first();
        $this->actingAs($user);

        $response = $this->post('/posts', [
            'title' => 'Test Post',
            'content' => 'This is a test post.',
            'scheduled_time' => now()->addHour()->toDateTimeString(),
            'status' => 'scheduled',
            'platforms' => [$platform->id],
        ]);

        $response->assertRedirect(route('dashboard'))
            ->assertSessionHas('success', 'Post created successfully and scheduled for publishing.');
        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'user_id' => $user->id,
            'status' => 'scheduled',
        ]);
        $this->assertDatabaseHas('post_platform', [
            'platform_id' => $platform->id,
            'platform_status' => 'pending',
        ]);
    }

    public function test_update_modifies_post_and_platforms()
    {
        $user = User::first();
        $post = Post::where('status', 'scheduled')->first();
        $platform = Platform::where('name', 'Twitter')->first();
        $this->actingAs($user);

        $response = $this->put("/posts/{$post->id}", [
            'title' => 'Updated Post',
            'content' => 'Updated content.',
            'scheduled_time' => now()->addDays(2)->toDateTimeString(),
            'status' => 'scheduled',
            'platforms' => [$platform->id],
        ]);

        $response->assertRedirect(route('dashboard'))
            ->assertSessionHas('success', 'Post updated successfully.');
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Post',
        ]);
        $this->assertDatabaseHas('post_platform', [
            'post_id' => $post->id,
            'platform_id' => $platform->id,
            'platform_status' => 'pending',
        ]);
    }

    public function test_cancel_changes_scheduled_post_to_draft()
    {
        $user = User::first();
        $post = Post::where('status', 'scheduled')->first();
        $this->actingAs($user);

        $response = $this->post("/posts/{$post->id}/cancel");

        $response->assertRedirect(route('dashboard'))
            ->assertSessionHas('success', 'Post has been canceled and moved to drafts.');
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'status' => 'draft',
        ]);
    }

    public function test_reschedule_updates_scheduled_time_and_logs_activity()
    {
        $user = User::first();
        $post = Post::where('status', 'scheduled')->first();
        $this->actingAs($user);

        $newTime = now()->addDays(2)->toDateTimeString();
        $response = $this->postJson("/posts/{$post->id}/reschedule", [
            'scheduled_time' => $newTime,
        ]);

        $response->assertRedirect(route('dashboard'))
            ->assertSessionHas('success', 'Post has been rescheduled successfully.');
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'scheduled_time' => $newTime,
            'status' => 'scheduled',
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'post_rescheduled',
            'details' => 'Rescheduled post: ' . $post->title,
        ]);
    }
}
