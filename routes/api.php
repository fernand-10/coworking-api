<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\SpaceController;
use App\Http\Controllers\Api\SpaceTypeController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Rutas públicas de autenticación (RF11)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas por Sanctum
Route::middleware('auth:sanctum')->group(function () {

    // Perfil y cierre de sesión (RF12, RF13)
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Consulta de espacios y tipos (RF01)
    Route::get('/spaces', [SpaceController::class, 'index']);
    Route::get('/spaces/{space}', [SpaceController::class, 'show']);
    Route::get('/space-types', [SpaceTypeController::class, 'index']);

    // Reservas del Cliente (RF02, RF03)
    Route::get('/my-reservations', [ReservationController::class, 'myReservations']);
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::patch('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel']);

    // Gestión de Staff / Recepción (RF04, RF05, RF06, RF07)
    Route::middleware('role:staff|admin')->group(function () {
        Route::get('/reservations/pending', [ReservationController::class, 'pending']);
        Route::patch('/reservations/{reservation}/status', [ReservationController::class, 'approveOrReject']);
        Route::patch('/reservations/{reservation}/complete', [ReservationController::class, 'complete']);
        Route::get('/spaces-occupancy', [SpaceController::class, 'dailyOccupancy']);
    });

    // Administración completa (RF08, RF09, RF10)
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('/space-types', SpaceTypeController::class)->except(['index']);
        Route::apiResource('/spaces', SpaceController::class)->except(['index', 'show']);

        Route::apiResource('/users', UserController::class);
        Route::post('/users/{user}/role', [UserController::class, 'assignRole']);

        Route::get('/roles', [RoleController::class, 'index']);
        Route::post('/roles', [RoleController::class, 'store']);
        Route::post('/roles/{role}/permissions', [RoleController::class, 'assignPermissions']);
        Route::get('/permissions', [RoleController::class, 'permissions']);
    });
});
