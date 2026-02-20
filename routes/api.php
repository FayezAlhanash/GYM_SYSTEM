<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CoachController;
use App\Http\Controllers\PlayerController;
use App\Http\Controllers\SubscriptionController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Coach
Route::post('/register', [CoachController::class, 'register']);
Route::post('/login', [CoachController::class, 'login']);

// Admin
Route::post('/admin/login', [AdminController::class, 'login']);


/*
|--------------------------------------------------------------------------
| Coach Protected Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // Coach
    Route::get('/profile', [CoachController::class, 'profile']);
    Route::post('/logout', [CoachController::class, 'logout']);

    // Players
    Route::get('/players', [PlayerController::class, 'index']);
    Route::get('/players/search', [PlayerController::class, 'search']);
    Route::post('/players', [PlayerController::class, 'store']);
    Route::put('/players/{id}', [PlayerController::class, 'update']);
    Route::delete('/players/{id}', [PlayerController::class, 'destroy']);
    Route::get('/dashboard', [PlayerController::class, 'dashboard']);

    // Subscriptions
    Route::post('/players/{id}/subscription/create', [SubscriptionController::class, 'createSub']);
    Route::post('/players/{id}/subscription/renew', [SubscriptionController::class, 'renew']);
    Route::get('/players/{id}/subscription/current', [SubscriptionController::class, 'current']);
    Route::get('/players/expired', [PlayerController::class, 'expired']);

});


/*
|--------------------------------------------------------------------------
| Admin Protected Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:admin')->group(function () {

    Route::get('/admin/coaches/pending', [AdminController::class, 'pendingCoaches']);
    Route::post('/admin/coaches/{id}/approve', [AdminController::class, 'approveCoach']);
    Route::post('/admin/coaches/{id}/reject', [AdminController::class, 'rejectCoach']);

});
