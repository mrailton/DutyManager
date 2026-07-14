<?php

declare(strict_types=1);

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MembersController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');

Route::prefix('members')->as('members.')->group(function (): void {
    Route::get('/', [MembersController::class, 'index'])->name('index');
});
