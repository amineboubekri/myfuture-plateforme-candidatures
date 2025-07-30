<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Authentication
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);
Route::post('/register', [App\Http\Controllers\AuthController::class, 'register']);
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->middleware('auth:sanctum');

// Student routes
Route::middleware(['auth:sanctum', 'role:student'])->prefix('student')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Student\DashboardController::class, 'index']);
    Route::get('/application', [App\Http\Controllers\Student\ApplicationController::class, 'show']);
    Route::post('/documents/upload', [App\Http\Controllers\Student\DocumentController::class, 'upload']);
    Route::get('/messages', [App\Http\Controllers\Student\MessageController::class, 'index']);
});

// Admin routes
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index']);
    Route::get('/applications', [App\Http\Controllers\Admin\ApplicationController::class, 'index']);
    Route::put('/applications/{id}/status', [App\Http\Controllers\Admin\ApplicationController::class, 'updateStatus']);
    Route::post('/messages/send', [App\Http\Controllers\Admin\MessageController::class, 'send']);
    Route::get('/reports', [App\Http\Controllers\Admin\ReportController::class, 'index']);
    Route::get('/documents', [App\Http\Controllers\Admin\DocumentController::class, 'index']);
    Route::put('/documents/{id}/validate', [App\Http\Controllers\Admin\DocumentController::class, 'validateDocument']);
});
