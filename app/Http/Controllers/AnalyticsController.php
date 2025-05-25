<?php

namespace App\Http\Controllers;

use App\Http\Requests\TopPostsRequest;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Str;

class AnalyticsController extends Controller
{
    /**
     * Display the analytics dashboard.
     */
    public function index()
    {
        $userId = Auth::id();
        $cacheKey = "analytics_{$userId}";

        // Cache analytics data for 15 minutes
        $data = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($userId) {
            // Get total posts count once
            $totalPosts = Post::where('user_id', $userId)->count();

            // Get posts by platform with published counts
            $postsByPlatform = DB::table('post_platform')
                ->join('posts', 'post_platform.post_id', '=', 'posts.id')
                ->join('platforms', 'post_platform.platform_id', '=', 'platforms.id')
                ->where('posts.user_id', $userId)
                ->select(
                    'platforms.name',
                    'platforms.type',
                    DB::raw('count(*) as count'),
                    DB::raw('sum(case when post_platform.platform_status = "published" then 1 else 0 end) as published_count')
                )
                ->groupBy('platforms.id', 'platforms.name', 'platforms.type')
                ->get()
                ->map(function ($item) use ($totalPosts) {
                    return [
                        'name' => $item->name,
                        'type' => $item->type,
                        'count' => $item->count,
                        'published_count' => $item->published_count,
                        'percentage' => $totalPosts > 0 ? round($item->count / $totalPosts * 100, 1) : 0,
                        'success_rate' => $item->count > 0 ? round($item->published_count / $item->count * 100, 1) : 0
                    ];
                });

            // Get scheduled and published post counts in one query
            $statusCounts = Post::where('user_id', $userId)
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            // Get time-series data
            $weekData = Post::where('user_id', $userId)
                ->where('scheduled_time', '>=', now()->subWeek())
                ->select(
                    DB::raw('DATE(scheduled_time) as date'),
                    DB::raw('sum(case when status = "scheduled" then 1 else 0 end) as scheduled_count'),
                    DB::raw('sum(case when status = "published" then 1 else 0 end) as published_count')
                )
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $monthData = Post::where('user_id', $userId)
                ->where('scheduled_time', '>=', now()->subMonth())
                ->select(
                    DB::raw('WEEK(scheduled_time) as week'),
                    DB::raw('sum(case when status = "scheduled" then 1 else 0 end) as scheduled_count'),
                    DB::raw('sum(case when status = "published" then 1 else 0 end) as published_count')
                )
                ->groupBy('week')
                ->orderBy('week')
                ->get();

            $yearData = Post::where('user_id', $userId)
                ->where('scheduled_time', '>=', now()->subYear())
                ->select(
                    DB::raw('MONTH(scheduled_time) as month'),
                    DB::raw('sum(case when status = "scheduled" then 1 else 0 end) as scheduled_count'),
                    DB::raw('sum(case when status = "published" then 1 else 0 end) as published_count')
                )
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            // Get top posts with optimized platform fields
            $topPosts = Post::where('user_id', $userId)
                ->with(['platforms' => fn($query) => $query->select('platforms.id', 'platforms.name', 'post_platform.platform_status')])
                ->select('id', 'title', 'content', 'image_url', 'scheduled_time', 'status')
                ->orderBy('scheduled_time', 'desc')
                ->limit(5)
                ->get();

            return [
                'postsByPlatform' => $postsByPlatform,
                'scheduledCount' => $statusCounts['scheduled'] ?? 0,
                'publishedCount' => $statusCounts['published'] ?? 0,
                'weekData' => $weekData,
                'monthData' => $monthData,
                'yearData' => $yearData,
                'topPosts' => $topPosts
            ];
        });

        return view('pages.analytics', $data);
    }

    /**
     * Get top posts based on range.
     */
    public function topPosts(TopPostsRequest $request)
    {
        $range = $request->range;
        $userId = Auth::id();
        $cacheKey = "top_posts_{$userId}_{$range}";

        // Cache top posts for 15 minutes
        $topPosts = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($userId, $range) {
            $query = Post::where('user_id', $userId)
                ->where('status', 'published')
                ->with(['platforms' => fn($query) => $query->select('platforms.id', 'platforms.name', 'post_platform.platform_status')])
                ->select('id', 'title', 'content', 'image_url', 'scheduled_time')
                ->orderBy('scheduled_time', 'desc')
                ->limit(5);

            switch ($range) {
                case '30days':
                    $query->where('scheduled_time', '>=', now()->subDays(30));
                    break;
                case '90days':
                    $query->where('scheduled_time', '>=', now()->subDays(90));
                    break;
                case 'year':
                    $query->where('scheduled_time', '>=', now()->subYear());
                    break;
                case 'all':
                    // No date filter
                    break;
            }

            return $query->get()->map(function ($post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'content' => Str::limit($post->content, 100),
                    'image_url' => $post->image_url,
                    'scheduled_time' => $post->scheduled_time->toDateTimeString(),
                    'platforms' => $post->platforms->map(function ($platform) {
                        return [
                            'name' => $platform->name,
                            'pivot' => ['platform_status' => $platform->pivot->platform_status]
                        ];
                    })
                ];
            });
        });

        return response()->json([
            'success' => true,
            'posts' => $topPosts
        ]);
    }
}
