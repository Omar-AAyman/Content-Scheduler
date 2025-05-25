<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\ActivityLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PublishScheduledPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'posts:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish scheduled posts that are due';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting scheduled post publishing...');

        // Get posts that are due for publishing
        $posts = Post::dueForPublishing()->get();

        $this->info("Found {$posts->count()} posts to publish.");

        foreach ($posts as $post) {
            $this->info("Processing post ID: {$post->id} - {$post->title}");

            try {
                // Load platforms
                $post->load(['platforms' => fn($query) => $query->select('platforms.id', 'platforms.name')]);

                // In a real application, this would call platform-specific APIs
                foreach ($post->platforms as $platform) {
                    $this->info("Publishing to {$platform->name}...");

                    // Update platform pivot status
                    $post->platforms()->updateExistingPivot($platform->id, [
                        'platform_status' => 'published',
                    ]);
                }

                // Update post status
                $post->status = 'published';
                $post->save();

                // Log activity
                ActivityLog::create([
                    'user_id' => $post->user_id,
                    'action' => 'post_published',
                    'details' => 'Post published: ' . $post->title,
                    'metadata' => [
                        'post_id' => $post->id,
                        'platforms' => $post->platforms->pluck('name')->toArray()
                    ]
                ]);

                $this->info("Post ID: {$post->id} published successfully.");
            } catch (\Exception $e) {
                $errorMessage = "Error publishing post ID: {$post->id} - {$e->getMessage()}";
                $this->error($errorMessage);

                // Update post status to failed
                $post->status = 'failed';
                $post->save();

                // Log error
                Log::error($errorMessage, [
                    'post_id' => $post->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                // Log activity
                ActivityLog::create([
                    'user_id' => $post->user_id,
                    'action' => 'post_publish_failed',
                    'details' => 'Failed to publish post: ' . $post->title,
                    'metadata' => [
                        'post_id' => $post->id,
                        'error' => $e->getMessage()
                    ]
                ]);
            }

            // Invalidate caches
            Cache::forget("dashboard_{$post->user_id}_");
            Cache::forget("analytics_{$post->user_id}");
        }

        $this->info('Scheduled post publishing completed.');

        return Command::SUCCESS;
    }
}
