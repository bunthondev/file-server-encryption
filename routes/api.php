<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\FileController;
use App\Http\Middleware\ApiAuthenticate;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware([ApiAuthenticate::class])->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // File routes
    Route::post('/files', [FileController::class, 'upload']);
    Route::get('/files', [FileController::class, 'index']);
    Route::get('/files/{file}/download', [FileController::class, 'download']);
    Route::get('/files/{file}/view', [FileController::class, 'view']);
    Route::delete('/files/{file}', [FileController::class, 'destroy']);
});
