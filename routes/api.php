<?php

use App\Http\Controllers\Api\RenderController;
use App\Http\Controllers\Api\TemplateController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    Route::post('/render', RenderController::class)->middleware('ability:render');
    Route::apiResource('templates', TemplateController::class)->middleware('ability:templates');
});
