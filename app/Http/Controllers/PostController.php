<?php

namespace App\Http\Controllers;

use App\Http\Requests\Posts\StoreRequest as StorePostRequest;
use App\Http\Requests\Posts\UpdateRequest as UpdatePostRequest;
use App\Http\Requests\Posts\RescheduleRequest as ReschedulePostRequest;
use App\Models\Post;
use App\Models\ActivityLog;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the posts.
     */
    public function index(Request $request)
    {
        // Retrieve filter parameters
        $status = $request->query('status', 'all');
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        // Build query for user's posts
        $query = Post::with(['platforms', 'user'])->where('user_id', Auth::id());

        // Apply filters
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($start_date) {
            $query->whereDate('scheduled_time', '>=', $start_date);
        }

        if ($end_date) {
            $query->whereDate('scheduled_time', '<=', $end_date);
        }

        // Paginate results (10 per page)
        $posts = $query->orderBy('scheduled_time', 'desc')->paginate(10);

        // Calculate post counts by status
        $postCounts = Post::where('user_id', Auth::id())
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $scheduledCount = $postCounts['scheduled'] ?? 0;
        $publishedCount = $postCounts['published'] ?? 0;
        $draftCount = $postCounts['draft'] ?? 0;
        $failedCount = $postCounts['failed'] ?? 0;

        // Calculate platform statistics
        $platformStats = DB::table('post_platform')
            ->join('posts', 'post_platform.post_id', '=', 'posts.id')
            ->join('platforms', 'post_platform.platform_id', '=', 'platforms.id')
            ->where('posts.user_id', Auth::id())
            ->select('platforms.name', DB::raw('count(*) as count'))
            ->groupBy('platforms.id', 'platforms.name')
            ->get();

        $totalPlatformPosts = $platformStats->sum('count');
        $platformStats = $platformStats->map(function ($item) use ($totalPlatformPosts) {
            return (object) [
                'name' => $item->name,
                'count' => $item->count,
                'percentage' => $totalPlatformPosts > 0
                    ? round($item->count / $totalPlatformPosts * 100, 1)
                    : 0
            ];
        });

        // Retrieve recent activities
        $activities = ActivityLog::where('user_id', Auth::id())
            ->where(function ($query) {
                $query->where('action', 'like', 'post_%')
                    ->orWhere('action', 'like', 'platform_%');
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Check if user has active platforms (cached)
        $hasActivePlatforms = Auth::user()->platforms()->wherePivot('is_active', true)->exists();

        // Return view with preserved query parameters
        return view('pages.dashboard', compact(
            'posts',
            'status',
            'start_date',
            'end_date',
            'scheduledCount',
            'publishedCount',
            'draftCount',
            'failedCount',
            'platformStats',
            'activities',
            'hasActivePlatforms'
        ))->withQuery($request->query());
    }

    /**
     * Show the form for creating a new post.
     */
    public function create()
    {
        $user = Auth::user();

        if (!$user->platforms()->wherePivot('is_active', true)->exists()) {
            return redirect()->route('dashboard')
                ->with('error', 'You must activate at least one platform before creating a post.');
        }

        $platforms = $user->platforms()->wherePivot('is_active', true)->get();
        $emailHandle = explode('@', $user->email)[0];

        return view('pages.post-editor', [
            'platforms' => $platforms,
            'post' => null,
            'userName' => $user->name,
            'emailHandle' => $emailHandle
        ]);
    }

    /**
     * Store a newly created post in storage.
     */
    public function store(StorePostRequest $request)
    {
        // Create post
        $post = Post::create([
            'title' => $request->title,
            'content' => $request->content,
            'image_url' => $request->image_url,
            'scheduled_time' => $request->scheduled_time,
            'status' => $request->status ?? 'scheduled',
            'user_id' => Auth::id()
        ]);

        // Attach platforms
        foreach ($request->platforms as $platformId) {
            $post->platforms()->attach($platformId, [
                'platform_status' => 'pending',
            ]);
        }

        return redirect()->route('dashboard')
            ->with('success', 'Post created successfully and scheduled for publishing.');
    }

    /**
     * Display the specified post.
     */
    public function show(Post $post)
    {
        $this->authorize('view', $post);

        $post->load('platforms', 'user');

        return view('pages.post-show', [
            'post' => $post,
            'userName' => $post->user->name,
            'emailHandle' => explode('@', $post->user->email)[0]
        ]);
    }

    /**
     * Show the form for editing the specified post.
     */
    public function edit(Post $post)
    {
        $this->authorize('update', $post);

        if ($post->status === 'published') {
            return redirect()->route('dashboard')
                ->with('error', 'Cannot edit a published post.');
        }

        $user = Auth::user();

        if (!$user->platforms()->wherePivot('is_active', true)->exists()) {
            return redirect()->route('dashboard')
                ->with('error', 'You must activate at least one platform before editing a post.');
        }

        $platforms = $user->platforms()->wherePivot('is_active', true)->get();
        $post->load('platforms');
        $user = Auth::user();
        $emailHandle = explode('@', $user->email)[0];

        return view('pages.post-editor', [
            'post' => $post,
            'platforms' => $platforms,
            'userName' => $user->name,
            'emailHandle' => $emailHandle
        ]);
    }

    /**
     * Update the specified post in storage.
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        $post->update([
            'title' => $request->title,
            'content' => $request->content,
            'image_url' => $request->image_url,
            'scheduled_time' => $request->scheduled_time,
            'status' => $request->status ?? $post->status
        ]);

        $syncData = [];
        foreach ($request->platforms as $platformId) {
            $syncData[$platformId] = [
                'platform_status' => 'pending',
            ];
        }
        $post->platforms()->sync($syncData);

        return redirect()->route('dashboard')
            ->with('success', 'Post updated successfully.');
    }

    /**
     * Remove the specified post from storage.
     */
    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        $post->delete();

        return redirect()->route('dashboard')
            ->with('success', 'Post deleted successfully.');
    }

    /**
     * Cancel a scheduled post.
     */
    public function cancel(Post $post)
    {
        $this->authorize('update', $post);

        if ($post->status !== 'scheduled') {
            return redirect()->back()
                ->with('error', 'Only scheduled posts can be canceled.');
        }

        $post->update([
            'status' => 'draft'
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Post has been canceled and moved to drafts.');
    }

    /**
     * Reschedule a post.
     */
    public function reschedule(ReschedulePostRequest $request, Post $post)
    {
        $post->update([
            'scheduled_time' => $request->scheduled_time,
            'status' => 'scheduled'
        ]);

        if ($post->status === 'failed') {
            foreach ($post->platforms as $platform) {
                $post->platforms()->updateExistingPivot($platform->id, [
                    'platform_status' => 'pending'
                ]);
            }
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => 'post_rescheduled',
            'details' => 'Rescheduled post: ' . $post->title,
            'metadata' => [
                'post_id' => $post->id,
                'scheduled_time' => $post->scheduled_time->toDateTimeString()
            ]
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Post has been rescheduled successfully.');
    }
}
