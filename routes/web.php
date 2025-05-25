<?php

use App\Http\Controllers\ActivityController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Authentication
Route::redirect('/', '/login');
Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'login');
    Route::get('/register', 'showRegistrationForm')->name('register');
    Route::post('/register', 'register');
    Route::post('/logout', 'logout')->name('logout');
});

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [PostController::class, 'index'])->name('dashboard');

    Route::controller(ProfileController::class)->prefix('profile')->group(function () {
        Route::get('/', 'show')->name('profile.show');
        Route::put('/', 'update')->name('profile.update');
    });

    Route::prefix('posts')->group(function () {
        Route::resource('/', PostController::class);
        Route::post('/{post}/cancel', [PostController::class, 'cancel'])->name('posts.cancel');
        Route::post('/{post}/reschedule', [PostController::class, 'reschedule'])->name('posts.reschedule');
    });

    Route::controller(PlatformController::class)->prefix('platforms')->group(function () {
        Route::get('/', 'index')->name('platforms.index');
        Route::post('/{platform}/toggle', 'toggle')->name('platforms.toggle');
        Route::post('/{platform}/limits', 'updateLimits')->name('platforms.limits');
    });

    Route::controller(AnalyticsController::class)->prefix('analytics')->group(function () {
        Route::get('/', 'index')->name('analytics.index');
        Route::get('/platform/{platform}', 'platformAnalytics')->name('analytics.platform');
        Route::get('/post/{post}', 'postAnalytics')->name('analytics.post');
        Route::post('/export', 'export')->name('analytics.export');
        Route::get('/top-posts', 'topPosts')->name('analytics.top_posts');
    });

    Route::get('/activities', [ActivityController::class, 'index'])->name('activities.index');
});
