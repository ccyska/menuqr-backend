<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\OrderController;


// ====================
// AUTH
// ====================

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


// ====================
// PUBLIC
// ====================

// Bisa diakses customer tanpa login
Route::get('/restaurants', [RestaurantController::class, 'index']);
Route::get('/restaurants/{slug}/menu', [RestaurantController::class, 'menu']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/menus', [MenuController::class, 'index']);
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/{id}', [OrderController::class, 'show']);
Route::get('/orders/{id}/whatsapp', [OrderController::class, 'whatsapp']);

// ====================
// ADMIN
// ====================

Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // RESTAURANT
    Route::post('/restaurants', [RestaurantController::class, 'store']);
    Route::put('/restaurants/{id}', [RestaurantController::class, 'update']);
    Route::delete('/restaurants/{id}', [RestaurantController::class, 'destroy']);
    Route::get('/orders', [OrderController::class, 'index']);


    // CATEGORY
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);


    // MENU
    Route::post('/menus', [MenuController::class, 'store']);
    Route::put('/menus/{id}', [MenuController::class, 'update']);
    Route::delete('/menus/{id}', [MenuController::class, 'destroy']);


    // TABLE / MEJA
    Route::get('/tables', [TableController::class, 'index']);
    Route::post('/tables', [TableController::class, 'store']);
    Route::put('/tables/{id}', [TableController::class, 'update']);
    Route::delete('/tables/{id}', [TableController::class, 'destroy']);


    // QR CODE
    Route::get('/tables/{id}/qr', [TableController::class, 'generateQr']);
    Route::get('/tables/{id}/qr/download', [TableController::class, 'downloadQr']);
});