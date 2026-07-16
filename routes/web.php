<?php

use App\Http\Controllers\Admin\AiProviderController;
use App\Http\Controllers\Admin\DataConnectionController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SystemHealthController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    return view('welcome', [
        'modules' => collect(config('ai_dashboard.modules'))
            ->sortBy('display_order')
            ->values(),
        'stack' => [
            'local_php' => PHP_VERSION,
            'app_env' => config('app.env'),
            'timezone' => config('app.timezone'),
        ],
    ]);
})->name('home');

Route::get('/health', function () {
    return response()->json([
        'application' => config('app.name'),
        'status' => 'ok',
        'timestamp' => now()->toIso8601String(),
        'environment' => config('app.env'),
    ]);
})->name('health.public');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    Route::middleware('permission:help.view')->group(function () {
        Route::get('/help', [HelpController::class, 'index'])->name('help.index');
        Route::get('/help/{slug}', [HelpController::class, 'show'])->name('help.show');
    });

    Route::get('/modules/{module}', [ModuleController::class, 'show'])
        ->middleware('module.access')
        ->name('modules.show');

    Route::prefix('administration')->name('admin.')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])
            ->middleware('permission:admin.users.view')
            ->name('users.index');
        Route::get('/roles', [RoleController::class, 'index'])
            ->middleware('permission:admin.roles.view')
            ->name('roles.index');
        Route::get('/system-health', [SystemHealthController::class, 'index'])
            ->middleware('permission:admin.system-health.view')
            ->name('system-health.index');

        Route::post('/data-connections/{dataConnection}/test', [DataConnectionController::class, 'test'])
            ->middleware('permission:admin.connections.test')
            ->name('data-connections.test');
        Route::resource('/data-connections', DataConnectionController::class)
            ->middleware('permission:admin.connections.manage');

        Route::post('/ai-providers/{aiProvider}/test', [AiProviderController::class, 'test'])
            ->middleware('permission:admin.ai-providers.test')
            ->name('ai-providers.test');
        Route::resource('/ai-providers', AiProviderController::class)
            ->middleware('permission:admin.ai-providers.manage');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
