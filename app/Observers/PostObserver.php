<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostObserver
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the Post "created" event.
     */
    public function created(Post $post)
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'post_created',
            'details' => 'Created post: ' . $post->title,
            'metadata' => [
                'post_id' => $post->id,
                'scheduled_time' => $post->scheduled_time->toDateTimeString(),
                'platforms' => $this->request->input('platforms', [])
            ]
        ]);
    }

    /**
     * Handle the Post "updated" event.
     */
    public function updated(Post $post)
    {
        $action = $post->status === 'draft' && $post->getOriginal('status') === 'scheduled'
            ? 'post_canceled'
            : 'post_updated';

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'details' => ($action === 'post_canceled' ? 'Canceled scheduled post: ' : 'Updated post: ') . $post->title,
            'metadata' => [
                'post_id' => $post->id,
                'scheduled_time' => $post->scheduled_time->toDateTimeString(),
                'platforms' => $this->request->input('platforms', [])
            ]
        ]);
    }

    /**
     * Handle the Post "deleted" event.
     */
    public function deleted(Post $post)
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'post_deleted',
            'details' => 'Deleted post: ' . $post->title,
            'metadata' => [
                'post_id' => $post->id
            ]
        ]);
    }
}
