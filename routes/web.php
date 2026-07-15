<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ProcessLogoutController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\SendPasswordResetLinkController;
use App\Http\Controllers\Auth\ShowLoginController;
use App\Http\Controllers\Auth\StoreLoginController;
use App\Http\Controllers\Auth\UpdatePasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Duties\DeleteDutyController;
use App\Http\Controllers\Duties\ListDutiesController;
use App\Http\Controllers\Duties\StoreDutyController;
use App\Http\Controllers\Duties\UpdateDutyController;
use App\Http\Controllers\Duties\ViewDutyController;
use App\Http\Controllers\Members\ListMembersController;
use App\Http\Controllers\Members\StoreMemberController;
use App\Http\Controllers\Members\UpdateMemberController;
use App\Http\Controllers\Members\ViewMemberController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Users\DeleteUserController;
use App\Http\Controllers\Users\ListUsersController;
use App\Http\Controllers\Users\ReactivateUserController;
use App\Http\Controllers\Users\StoreUserController;
use App\Http\Controllers\Users\UpdateUserController;
use App\Http\Controllers\Users\ViewUserController;
use App\Http\Controllers\Vehicles\ListVehiclesController;
use App\Http\Controllers\Vehicles\StoreVehicleController;
use App\Http\Controllers\Vehicles\UpdateVehicleController;
use App\Http\Controllers\Vehicles\ViewVehicleController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/search', SearchController::class)->name('search');

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
        Route::delete('/{duty}', DeleteDutyController::class)->name('delete');
    });

    Route::prefix('users')->as('users.')->group(function (): void {
        Route::get('/', ListUsersController::class)->name('index');
        Route::post('/', StoreUserController::class)->name('store');
        Route::get('/{user}', ViewUserController::class)->name('show');
        Route::put('/{user}', UpdateUserController::class)->name('update');
        Route::delete('/{user}', DeleteUserController::class)->name('delete');
        Route::post('/{user}/reactivate', ReactivateUserController::class)->name('reactivate')->withTrashed();
    });

    Route::post('/logout', ProcessLogoutController::class)->name('logout');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', ShowLoginController::class)->name('login');
    Route::post('/login', StoreLoginController::class)->name('login.store');

    Route::get('/forgot-password', ForgotPasswordController::class)->name('password.request');
    Route::post('/forgot-password', SendPasswordResetLinkController::class)->name('password.email');
    Route::get('/reset-password/{token}', ResetPasswordController::class)->name('password.reset');
    Route::post('/reset-password', UpdatePasswordController::class)->name('password.update');
});
