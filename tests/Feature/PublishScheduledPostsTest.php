<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use App\Models\Platform;
use App\Console\Commands\PublishScheduledPosts;
use Database\Seeders\TestDatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

class PublishScheduledPostsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TestDatabaseSeeder::class);
    }

    public function test_command_publishes_due_posts_and_logs_activity()
    {
        $user = User::first();
        $post = Post::where('status', 'scheduled')->first();
        $platform = Platform::where('name', 'Twitter')->first();

        $this->artisan(PublishScheduledPosts::class)
            ->expectsOutput("Found 5 posts to publish.")
            ->expectsOutput("Processing post ID: {$post->id} - {$post->title}")
            ->expectsOutput("Publishing to Twitter...")
            ->expectsOutput("Post ID: {$post->id} published successfully.")
            ->assertExitCode(0);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'status' => 'published',
        ]);
        $this->assertDatabaseHas('post_platform', [
            'post_id' => $post->id,
            'platform_id' => $platform->id,
            'platform_status' => 'published',
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'post_published',
            'details' => 'Post published: ' . $post->title,
        ]);
    }

    public function test_command_handles_publishing_failure()
    {
        $user = User::first();
        $post = Post::where('status', 'scheduled')->first();
        $platform = Platform::where('name', 'Twitter')->first();

        // Simulate exception by mocking platform update failure
        $this->mock(\Illuminate\Database\Eloquent\Relations\BelongsToMany::class, function ($mock) use ($platform) {
            $mock->shouldReceive('updateExistingPivot')->andThrow(new \Exception('API failure'));
        });

        Log::shouldReceive('error')->once();

        $this->artisan(PublishScheduledPosts::class)
            ->expectsOutput("Error publishing post ID: {$post->id} - API failure")
            ->assertExitCode(0);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'status' => 'failed',
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'post_publish_failed',
            'details' => 'Failed to publish post: ' . $post->title,
        ]);
    }
}
