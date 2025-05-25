<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    /**
     * Display a listing of the user's activity logs.
     */
    public function index()
    {
        $activities = ActivityLog::where('user_id', Auth::id())
            ->where(function ($query) {
                $query->where('action', 'like', 'post_%')
                    ->orWhere('action', 'like', 'platform_%');
            })
            ->orderBy('created_at', 'desc')
            ->get(['id', 'details', 'created_at'])
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'details' => $activity->details,
                    'created_at' => $activity->created_at,
                    'human_readable_date' => $activity->created_at->diffForHumans()
                ];
            });

        return response()->json([
            'activities' => $activities
        ]);
    }
}
