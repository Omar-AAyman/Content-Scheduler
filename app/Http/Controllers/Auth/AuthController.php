<?php

namespace App\Http\Controllers\Auth;


use App\Models\User;
use Illuminate\Http\Request;
use App\Events\Auth\UserLoginEvent;
use App\Events\Auth\UserLogoutEvent;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Events\Auth\LoginFailedEvent;
use App\Http\Requests\Auth\RegisterRequest;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('pages.auth.login');
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Dispatch login event
            event(new UserLoginEvent(Auth::user(), $request->ip(), $request->userAgent()));

            return redirect()->intended('dashboard');
        }

        // Dispatch failed login event
        event(new LoginFailedEvent($request->email, $request->ip(), $request->userAgent()));

        return redirect()->back()
            ->withErrors(['email' => 'These credentials do not match our records.'])
            ->withInput($request->except('password'));
    }

    /**
     * Show the registration form.
     */
    public function showRegistrationForm()
    {
        return view('pages.auth.register');
    }

    /**
     * Handle a registration request.
     */
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Account created successfully! Welcome to Content Scheduler.');
    }

    /**
     * Log the user out.
     */
    public function logout(Request $request)
    {
        if (Auth::check()) {
            // Dispatch logout event
            event(new UserLogoutEvent(Auth::user(), $request->ip(), $request->userAgent()));
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'You have been logged out successfully.');
    }
}
