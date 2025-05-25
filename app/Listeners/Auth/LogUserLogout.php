<?php

namespace App\Listeners\Auth;

use App\Events\Auth\UserLogoutEvent;
use App\Models\ActivityLog;


class LogUserLogout
{
    public function handle(UserLogoutEvent $event)
    {
        ActivityLog::create([
            'user_id' => $event->user->id,
            'action' => 'user_logout',
            'details' => 'User logged out',
            'metadata' => [
                'ip' => $event->ip,
                'user_agent' => $event->userAgent
            ]
        ]);
    }
}
