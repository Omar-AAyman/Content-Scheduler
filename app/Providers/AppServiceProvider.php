<?php

namespace App\Providers;

use App\Models\Platform;
use Illuminate\Support\ServiceProvider;
use App\Models\Post;
use App\Models\User;
use App\Observers\PlatformObserver;
use App\Observers\PostObserver;
use App\Observers\UserObserver;
use App\Policies\PostPolicy;
use Illuminate\Support\Facades\Gate;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Observers
            // User Observer
            User::observe(UserObserver::class);

            // Post Observer
            Post::observe(PostObserver::class);

            // Platform Observer
            Platform::observe(PlatformObserver::class);
        //

        
        // Register Post policies
        Gate::policy(Post::class, PostPolicy::class);
    }
}
