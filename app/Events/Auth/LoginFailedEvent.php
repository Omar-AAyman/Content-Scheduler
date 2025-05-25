<?php

namespace App\Events\Auth;

use App\Models\User;
use Illuminate\Queue\SerializesModels;

class LoginFailedEvent
{
    use SerializesModels;

    public $email;
    public $ip;
    public $userAgent;

    public function __construct(string $email, string $ip, string $userAgent)
    {
        $this->email = $email;
        $this->ip = $ip;
        $this->userAgent = $userAgent;
    }
}
