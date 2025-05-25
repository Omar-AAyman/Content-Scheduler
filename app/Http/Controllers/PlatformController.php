<?php

namespace App\Http\Controllers;

use App\Http\Requests\TogglePlatformRequest;
use App\Models\Platform;
use App\Models\Post;
use App\Observers\PlatformObserver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class PlatformController extends Controller
{
    /**
     * Display a listing of the platforms.
     */
    public function index()
    {
        $user = Auth::user();
        $userId = $user->id;
        $cacheKey = "platforms_{$userId}";

        // Ensure user-platform pivots are initialized
        $this->initializeUserPlatforms($user);

        // Cache only the platforms data for 15 minutes
        $platforms = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($userId) {
            return Platform::with(['users' => fn($query) => $query->where('user_id', $userId)
                ->select('users.id', 'user_platforms.is_active')])
                ->select('id', 'name', 'type', 'max_content_length')
                ->get();
        });

        // Calculate daily post count on every request (outside cache)
        $dailyPostCount = Post::where('user_id', $userId)
            ->where('status', 'scheduled')
            ->whereDate('scheduled_time', now()->toDateString())
            ->count();

        // Prepare data for the view
        $data = compact('platforms', 'dailyPostCount');

        return view('pages.platform-settings', $data);
    }

    /**
     * Toggle active status for a platform for the authenticated user.
     */
    public function toggle(TogglePlatformRequest $request, Platform $platform)
    {
        $user = Auth::user();
        $isActive = $request->is_active;
        $userId = $user->id;

        // Count active platforms before toggle
        $activePlatformsBefore = $user->platforms()->wherePivot('is_active', true)->count();

        // Update or create the user-platform pivot entry
        $user->platforms()->syncWithoutDetaching([
            $platform->id => ['is_active' => $isActive]
        ]);

        // Trigger the observer's pivotUpdated method
        (new PlatformObserver)->pivotUpdated($platform, $userId, $isActive);

        // Clear caches for this user's platforms
        Cache::forget("platforms_{$userId}");
        Cache::forget("user_{$userId}_active_platforms");

        // Count active platforms after toggle
        $activePlatformsAfter = $user->platforms()->wherePivot('is_active', true)->count();

        // Determine if reload is needed
        $needsReload = ($activePlatformsBefore === 0 && $activePlatformsAfter > 0) ||
            ($activePlatformsBefore === 1 && $activePlatformsAfter === 0);

        return response()->json([
            'success' => true,
            'message' => 'Platform status updated successfully',
            'platform' => [
                'id' => $platform->id,
                'name' => $platform->name,
                'is_active' => $isActive
            ],
            'reload' => $needsReload
        ]);
    }

    /**
     * Ensure the authenticated user has entries in the platform_user pivot table.
     */
    private function initializeUserPlatforms($user)
    {
        $existingPlatformIds = $user->platforms()->pluck('platform_id')->toArray();
        $allPlatformIds = Platform::pluck('id')->toArray();
        $missingPlatformIds = array_diff($allPlatformIds, $existingPlatformIds);

        if (!empty($missingPlatformIds)) {
            $syncData = array_fill_keys($missingPlatformIds, ['is_active' => true]);
            $user->platforms()->syncWithoutDetaching($syncData);
        }
    }
}
