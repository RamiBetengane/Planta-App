<?php
use App\Http\Controllers\DonerController;

use App\Http\Controllers\OwnerController;
use App\Http\Controllers\WorkshopController;
use Illuminate\Support\Facades\Route;

Route::prefix('owner')->group(function () {
    Route::post('/register', [OwnerController::class, 'register']);
    Route::post('/login', [OwnerController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [OwnerController::class, 'logout']);
        // راوتات ثانية محمية
        Route::get('/profile', [OwnerController::class, 'profile']);
        Route::put('/updateInfo', [OwnerController::class, 'updatePersonalInfo']);
    });
});



Route::prefix('donor')->group(function () {
    Route::post('/register', [DonerController::class, 'register']);
    Route::post('/login', [DonerController::class, 'login']);

    // المسارات المحمية للمُتبرع بعد تسجيل الدخول
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [DonerController::class, 'logout']);
        Route::put('/updateInfo', [DonerController::class, 'updatePersonalInfo']);
        Route::get('/profile', [DonerController::class, 'profile']);

    });
});



Route::prefix('workshop')->group(function () {

    Route::post('/register', [WorkshopController::class, 'register']);

    Route::post('/login', [WorkshopController::class, 'login']);

    // حماية بعض الروابط
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/logout', [WorkshopController::class, 'logout']);
        Route::get('/profile', [WorkshopController::class, 'profile']);
        Route::put('/update', [WorkshopController::class, 'updatePersonalInfo']);

    });
});
