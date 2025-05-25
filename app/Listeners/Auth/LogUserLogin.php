<?php

namespace App\Listeners\Auth;

use App\Events\Auth\UserLoginEvent;
use App\Models\ActivityLog;

class LogUserLogin
{
    public function handle(UserLoginEvent $event)
    {
        ActivityLog::create([
            'user_id' => $event->user->id,
            'action' => 'user_login',
            'details' => 'User logged in',
            'metadata' => [
                'ip' => $event->ip,
                'user_agent' => $event->userAgent
            ]
        ]);
    }
}
