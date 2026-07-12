<?php

use App\Http\Controllers\Api\RenderController;
use App\Http\Controllers\Api\TemplateController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/render', RenderController::class);
    Route::apiResource('templates', TemplateController::class);
});
