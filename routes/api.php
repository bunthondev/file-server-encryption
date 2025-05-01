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
    Route::get('/{bucket}/{file}/download', [FileController::class, 'downloadByPath']);
    Route::get('/{bucket}/{file}/view', [FileController::class, 'viewByPath']);
    Route::delete('/files/{bucket}/{file}', [FileController::class, 'destroy']);
});
