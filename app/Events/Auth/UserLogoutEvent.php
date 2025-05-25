<?php

namespace App\Events\Auth;

use App\Models\User;
use Illuminate\Queue\SerializesModels;

class UserLogoutEvent
{
    use SerializesModels;

    public $user;
    public $ip;
    public $userAgent;

    public function __construct(User $user, string $ip, string $userAgent)
    {
        $this->user = $user;
        $this->ip = $ip;
        $this->userAgent = $userAgent;
    }
}
