<?php

use App\Http\Middleware\ForceJsonResponse;
use App\Rendering\RenderException;
use App\Rendering\TemplateSyntaxException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            ForceJsonResponse::class,
        ]);

        $middleware->throttleApi();

        $middleware->alias([
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (RenderException $e, Request $request) {
            return response()->json([
                'message' => 'Render failed. The render engine is unavailable or returned an error, check the application log for details.',
            ], 503);
        });

        $exceptions->render(function (TemplateSyntaxException $e, Request $request) {
            return response()->json([
                'message' => 'Template syntax error: '.$e->getMessage(),
            ], 422);
        });
    })->create();
