<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\MetricsController;

Route::middleware(['cors'])->group(function () {
    Route::post('/contact', [ContactController::class, 'submit'])->middleware('throttle:10,1');
        

    Route::get('/health', [HealthController::class, 'check']);
    Route::get('/metrics', [MetricsController::class, 'stats']);
});