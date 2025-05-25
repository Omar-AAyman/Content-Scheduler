<?php

namespace App\Listeners\Auth;

use App\Events\Auth\LoginFailedEvent;
use App\Models\ActivityLog;

class LogLoginFailed
{
    public function handle(LoginFailedEvent $event)
    {
        ActivityLog::create([
            'user_id' => null,
            'action' => 'login_failed',
            'details' => 'Failed login attempt for email: ' . $event->email,
            'metadata' => [
                'ip' => $event->ip,
                'user_agent' => $event->userAgent
            ]
        ]);
    }
}
