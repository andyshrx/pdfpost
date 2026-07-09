<?php

namespace App\Providers;

use App\Rendering\GotenbergEngine;
use App\Rendering\RenderEngine;
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
        //
    }
}
