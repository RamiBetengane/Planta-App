<?php
use App\Http\Controllers\DonerController;

use App\Http\Controllers\OwnerController;
use App\Http\Controllers\WorkshopController;
use Illuminate\Support\Facades\Route;

Route::prefix('owner')->group(function () {
    Route::post('/register', [OwnerController::class, 'register']);
    Route::post('/login', [OwnerController::class, 'login']);

    Route::middleware(['auth:sanctum','role:land_owner'])->group(function () {
        Route::post('/logout', [OwnerController::class, 'logout']);
        Route::get('/profile', [OwnerController::class, 'getProfile']);
        Route::post('/addInfo', [OwnerController::class, 'completeProfile']);
        Route::post('/addLand', [OwnerController::class, 'addLand']);
        Route::get('/getLnadById/{id}', [OwnerController::class, 'getLnadById']);
        Route::get('/getAllLands', [OwnerController::class, 'getAllLands']);

    });
});

Route::prefix('donor')->group(function () {
    Route::post('/register', [DonerController::class, 'register']);
    Route::post('/login', [DonerController::class, 'login']);

    Route::middleware(['auth:sanctum', 'role:donor'])->group(function () {
        Route::post('/logout', [DonerController::class, 'logout']);
        Route::put('/updateInfo', [DonerController::class, 'updatePersonalInfo']);
        Route::get('/profile', [DonerController::class, 'profile']);
    });
});

Route::prefix('workshop')->group(function () {
    Route::post('/register', [WorkshopController::class, 'register']);
    Route::post('/login', [WorkshopController::class, 'login']);

    Route::middleware(['auth:sanctum', 'role:workshop'])->group(function () {
        Route::post('/logout', [WorkshopController::class, 'logout']);
        Route::get('/profile', [WorkshopController::class, 'profile']);
        Route::put('/update', [WorkshopController::class, 'updatePersonalInfo']);
    });
});
