<?php

use App\Http\Controllers\Api\AnalysisController;
use App\Http\Controllers\Api\HealthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', [HealthController::class, 'public'])->name('api.health.public');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/health/internal', [HealthController::class, 'internal'])
            ->middleware('permission:api.health.read')
            ->name('api.health.internal');
        Route::get('/analysis/{module}', [AnalysisController::class, 'show'])
            ->middleware('permission:api.analysis.read')
            ->name('api.analysis.show');
        Route::get('/dashboard/summary', [AnalysisController::class, 'dashboard'])
            ->middleware('permission:api.analysis.read')
            ->name('api.dashboard.summary');
    });
});
