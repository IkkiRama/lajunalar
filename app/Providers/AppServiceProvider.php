<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
        Model::preventLazyLoading(! app()->isProduction());

        Gate::define('viewApiDocs', fn ($user = null): bool => app()->isLocal() || $user?->role === 'admin');

        RateLimiter::for('google-auth', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));
        RateLimiter::for('attempt-start', fn (Request $request) => Limit::perMinute(8)->by((string) ($request->user()?->id ?? $request->ip())));
        RateLimiter::for('answer-batches', fn (Request $request) => Limit::perMinute(180)->by((string) ($request->user()?->id ?? $request->ip())));
    }
}
