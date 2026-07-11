<?php

use App\Http\Controllers\Api\RenderController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/render', RenderController::class);
});
