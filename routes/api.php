<?php

use App\Http\Controllers\DonerController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\OwnerController;
use App\Http\Controllers\WorkshopController;
use Illuminate\Support\Facades\Route;

Route::prefix('owner')->group(function () {
    Route::post('/register', [OwnerController::class, 'register']);
    Route::post('/login', [OwnerController::class, 'login']);

    Route::middleware(['auth:sanctum', 'role:land_owner'])->group(function () {
        Route::post('/logout', [OwnerController::class, 'logout']);
        Route::get('/profile', [OwnerController::class, 'getProfile']);
        Route::post('/addInfo', [OwnerController::class, 'completeProfile']);
        Route::post('/addLand', [OwnerController::class, 'addLand']);
        Route::get('/getLnadById/{id}', [OwnerController::class, 'getLnadById']);
        Route::get('/getAllLands', [OwnerController::class, 'getAllLands']);
        Route::get('/getAllPlants', [OwnerController::class, 'getAllPlants']);
        Route::get('/getPlantById/{id}', [OwnerController::class, 'getPlantById']);
        Route::get('/getPlanetsForSpecificLand/{id}', [OwnerController::class, 'getPlanetsForSpecificLand']);

        Route::post('/addRequest', [OwnerController::class, 'addRequest']);
        Route::get('/getAllRequests', [OwnerController::class, 'getAllRequests']);
        Route::get('/getRequestById/{id}', [OwnerController::class, 'getRequestById']);

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

Route::prefix('manager')->group(function () {
    Route::post('/login', [ManagerController::class, 'login']);

    Route::middleware(['auth:sanctum', 'role:manager'])->group(function () {
        Route::post('/logout', [ManagerController::class, 'logout']);
        Route::get('/profile', [ManagerController::class, 'profile']);
        Route::post('/update', [ManagerController::class, 'updatePersonalInfo']);
        Route::put('/requests/{id}/review', [ManagerController::class, 'reviewRequest']);
        Route::post('/createTender', [ManagerController::class, 'createTender']);
        Route::put('/update/{id}', [ManagerController::class, 'update']);
        Route::delete('/destroy/{id}', [ManagerController::class, 'destroy']);
        Route::get('/getAllTenders', [ManagerController::class, 'getAllTenders']);
        Route::get('/getTenderById/{id}', [ManagerController::class, 'getTenderById']);


        Route::get('/getAllRequests', [OwnerController::class, 'getAllRequests']);

    });
});
