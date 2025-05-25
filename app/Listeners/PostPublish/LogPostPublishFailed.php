<?php

namespace App\Listeners\PostPublish;

use App\Events\PostPublish\PostPublishFailed;
use App\Models\ActivityLog;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogPostPublishFailed implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param PostPublishFailed $event
     * @return void
     */
    public function handle(PostPublishFailed $event)
    {
        ActivityLog::create([
            'user_id' => $event->post->user_id,
            'action' => 'post_publish_failed',
            'details' => 'Failed to publish post: ' . $event->post->title,
            'metadata' => [
                'post_id' => $event->post->id,
                'error' => $event->error
            ]
        ]);
    }
}