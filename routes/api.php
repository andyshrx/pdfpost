<?php

use App\Http\Controllers\Api\AsyncRenderController;
use App\Http\Controllers\Api\RenderArtifactController;
use App\Http\Controllers\Api\RenderController;
use App\Http\Controllers\Api\TemplateController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::post('/render', RenderController::class)->middleware('ability:render');
    Route::post('/renders', [AsyncRenderController::class, 'store'])->middleware('ability:render');
    Route::get('/renders/{render}', [AsyncRenderController::class, 'show'])->middleware('ability:render');
    Route::apiResource('templates', TemplateController::class)
        ->names('api.templates')
        ->middleware('ability:templates');
});

// artifact downloads use expiring signed urls instead of api tokens, so
// webhook receivers can fetch the file without holding credentials
Route::get('/v1/renders/{render}/artifact', RenderArtifactController::class)
    ->middleware('signed')
    ->name('api.renders.artifact');
