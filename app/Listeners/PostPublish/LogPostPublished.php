<?php

namespace App\Listeners\PostPublish;

use App\Events\PostPublish\PostPublished;
use App\Models\ActivityLog;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogPostPublished implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param PostPublished $event
     * @return void
     */
    public function handle(PostPublished $event)
    {
        ActivityLog::create([
            'user_id' => $event->post->user_id,
            'action' => 'post_published',
            'details' => 'Post published: ' . $event->post->title,
            'metadata' => [
                'post_id' => $event->post->id,
                'platforms' => $event->post->platforms->pluck('name')->toArray()
            ]
        ]);
    }
}