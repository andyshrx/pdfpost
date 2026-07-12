<?php

namespace App\Providers;

use App\Rendering\GotenbergEngine;
use App\Rendering\RenderEngine;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(RenderEngine::class, GotenbergEngine::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(config('pdfpost.rate_limit'))
                ->by($request->user()?->getAuthIdentifier() ?: $request->ip());
        });
    }
}
