<?php

namespace App\Events\PostPublish;

use App\Models\Post;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostPublishFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $post;
    public $error;

    /**
     * Create a new event instance.
     *
     * @param Post $post
     * @param string $error
     */
    public function __construct(Post $post, string $error)
    {
        $this->post = $post;
        $this->error = $error;
    }
}
