<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\ProcessLogoutController;
use App\Http\Controllers\Auth\ShowLoginController;
use App\Http\Controllers\Auth\StoreLoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Duties\ListDutiesController;
use App\Http\Controllers\Duties\StoreDutyController;
use App\Http\Controllers\Duties\UpdateDutyController;
use App\Http\Controllers\Duties\ViewDutyController;
use App\Http\Controllers\Members\ListMembersController;
use App\Http\Controllers\Members\StoreMemberController;
use App\Http\Controllers\Members\UpdateMemberController;
use App\Http\Controllers\Members\ViewMemberController;
use App\Http\Controllers\Vehicles\ListVehiclesController;
use App\Http\Controllers\Vehicles\StoreVehicleController;
use App\Http\Controllers\Vehicles\UpdateVehicleController;
use App\Http\Controllers\Vehicles\ViewVehicleController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::prefix('members')->as('members.')->group(function (): void {
        Route::get('/', ListMembersController::class)->name('index');
        Route::post('/', StoreMemberController::class)->name('store');
        Route::get('/{member}', ViewMemberController::class)->name('show');
        Route::put('/{member}', UpdateMemberController::class)->name('update');
    });

    Route::prefix('vehicles')->as('vehicles.')->group(function (): void {
        Route::get('/', ListVehiclesController::class)->name('index');
        Route::post('/', StoreVehicleController::class)->name('store');
        Route::get('/{vehicle}', ViewVehicleController::class)->name('show');
        Route::put('/{vehicle}', UpdateVehicleController::class)->name('update');
    });

    Route::prefix('duties')->as('duties.')->group(function (): void {
        Route::get('/', ListDutiesController::class)->name('index');
        Route::post('/', StoreDutyController::class)->name('store');
        Route::get('/{duty}', ViewDutyController::class)->name('show');
        Route::put('/{duty}', UpdateDutyController::class)->name('update');
    });

    Route::post('/logout', ProcessLogoutController::class)->name('logout');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', ShowLoginController::class)->name('login');
    Route::post('/login', StoreLoginController::class)->name('login.store');
});
