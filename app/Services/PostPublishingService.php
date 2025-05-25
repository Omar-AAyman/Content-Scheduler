<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Platform;
use Illuminate\Support\Facades\Log;

class PostPublishingService
{
    /**
     * Publish a post to its selected platforms.
     *
     * @param Post $post
     * @return bool
     */
    public function publishPost(Post $post)
    {
        try {
            // Load platforms
            $post->load('platforms');

            if ($post->platforms->isEmpty()) {
                // Update post status to failed
                $post->status = 'failed';
                $post->save();

                // Log error
                Log::warning("Post ID: {$post->id} has no platforms selected for publishing.");

                // Dispatch event
                event(new PostPublishFailed($post, 'No platforms selected'));

                return false;
            }

            // Update post status
            $post->status = 'published';
            $post->save();

            // Publish to each platform
            $allPlatformsPublished = true;
            foreach ($post->platforms as $platform) {
                if (!$this->publishToPlatform($post, $platform)) {
                    $allPlatformsPublished = false;
                }
            }

            // If any platform failed, mark post as failed
            if (!$allPlatformsPublished) {
                $post->status = 'failed';
                $post->save();

                // Dispatch event
                event(new PostPublishFailed($post, 'One or more platforms failed'));

                return false;
            }

            // Dispatch event for success
            event(new PostPublished($post));

            return true;
        } catch (\Exception $e) {
            $this->handlePublishingError($post, $e);
            return false;
        }
    }

    /**
     * Publish a post to a specific platform.
     *
     * @param Post $post
     * @param Platform $platform
     * @return bool
     */
    protected function publishToPlatform(Post $post, Platform $platform)
    {
        try {
            // Get platform-specific content or use default content
            $content = $post->content;

            // Simulate API call to platform
            $response = $this->simulatePlatformApiCall($platform, $post, $content);

            // Check API response (for future-proofing real API calls)
            if (!$response['success']) {
                throw new \Exception("API call failed for {$platform->name}");
            }

            // Update platform pivot status
            $post->platforms()->updateExistingPivot($platform->id, [
                'platform_status' => 'published',
            ]);

            Log::info("Post ID: {$post->id} published to {$platform->name} successfully.");
            return true;
        } catch (\Exception $e) {
            Log::error("Error publishing post ID: {$post->id} to {$platform->name}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Update platform pivot status
            $post->platforms()->updateExistingPivot($platform->id, [
                'platform_status' => 'failed',
            ]);

            return false;
        }
    }

    /**
     * Simulate an API call to a social media platform.
     *
     * @param Platform $platform
     * @param Post $post
     * @param string $content
     * @return array
     */
    protected function simulatePlatformApiCall(Platform $platform, Post $post, $content)
    {
        // In a real application, this would make an actual API call
        // For this example, we'll return a simulated successful response
        return [
            'success' => true,
            'message' => 'Post published successfully to ' . $platform->name,
            'timestamp' => now()->toDateTimeString(),
            'platform_post_id' => $platform->type . '_' . rand(1000000, 9999999),
            'platform' => $platform->name,
            'content_length' => strlen($content),
            'has_image' => !empty($post->image_url)
        ];
    }

    /**
     * Handle publishing errors.
     *
     * @param Post $post
     * @param \Exception $exception
     * @return void
     */
    protected function handlePublishingError(Post $post, \Exception $exception)
    {
        // Update post status to failed
        $post->status = 'failed';
        $post->save();

        // Log error
        Log::error("Failed to publish post: {$post->id}", [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Dispatch event
        event(new PostPublishFailed($post, $exception->getMessage()));
    }
}
