<?php

namespace App\Observers;

use App\Models\Platform;
use App\Models\ActivityLog;

class PlatformObserver
{
    /**
     * Handle custom pivot table update for user_platforms.
     */
    public function pivotUpdated(Platform $platform, $userId, $isActive)
    {
        // Log when is_active changes in user_platforms pivot
        ActivityLog::create([
            'user_id' => $userId,
            'action' => $isActive ? 'platform_activated' : 'platform_deactivated',
            'details' => ucfirst($platform->name) . ' platform ' . ($isActive ? 'activated' : 'deactivated'),
            'metadata' => [
                'platform_id' => $platform->id,
                'platform_name' => $platform->name
            ]
        ]);
    }
}
