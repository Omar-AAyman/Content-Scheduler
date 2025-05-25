<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Http\Request;

class UserObserver
{
    protected $request;

    // Inject the Request object (optional, depending on how you handle request data)
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the User "created" event (registration).
     */
    public function created(User $user)
    {
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'user_registered',
            'details' => 'New user registered',
            'metadata' => [
                'ip' => $this->request->ip(),
                'user_agent' => $this->request->userAgent()
            ]
        ]);


        // Attach all platforms to the user with is_active = true
        $platforms = Platform::all()->pluck('id')->toArray();
        $syncData = array_fill_keys($platforms, ['is_active' => true]);
        $user->platforms()->sync($syncData);
    }
}
