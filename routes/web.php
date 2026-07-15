<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\ProcessLogoutController;
use App\Http\Controllers\Auth\ShowLoginController;
use App\Http\Controllers\Auth\StoreLoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Members\ListMembersController;
use App\Http\Controllers\Members\StoreMemberController;
use App\Http\Controllers\Vehicles\ListVehiclesController;
use App\Http\Controllers\Vehicles\StoreVehicleController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::prefix('members')->as('members.')->group(function (): void {
        Route::get('/', ListMembersController::class)->name('index');
        Route::post('/', StoreMemberController::class)->name('store');
    });

    Route::prefix('vehicles')->as('vehicles.')->group(function (): void {
        Route::get('/', ListVehiclesController::class)->name('index');
        Route::post('/', StoreVehicleController::class)->name('store');
    });

    Route::post('/logout', ProcessLogoutController::class)->name('logout');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', ShowLoginController::class)->name('login');
    Route::post('/login', StoreLoginController::class)->name('login.store');
});
