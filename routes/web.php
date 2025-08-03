<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ChatbotController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->role === 'admin') {
            return redirect('/admin/dashboard');
        } else {
            return redirect('/student/dashboard');
        }
    }
    return redirect('/login');
});

// Auth
Route::get('/login', [App\Http\Controllers\AuthController::class, 'login'])->name('login');
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login']);
Route::get('/register', [App\Http\Controllers\AuthController::class, 'register'])->name('register');
Route::post('/register', [App\Http\Controllers\AuthController::class, 'register']);
Route::get('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');
Route::get('/pending-approval', [App\Http\Controllers\AuthController::class, 'pendingApproval'])->name('pending-approval');

// Email Verification Routes
Route::get('/email/verify', [App\Http\Controllers\AuthController::class, 'verifyNotice'])
    ->middleware('auth')
    ->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', [App\Http\Controllers\AuthController::class, 'verifyEmail'])
    ->middleware(['auth', 'signed'])
    ->name('verification.verify');

Route::post('/email/verification-notification', [App\Http\Controllers\AuthController::class, 'resendVerificationEmail'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

// Password Change
Route::get('/change-password', [App\Http\Controllers\AuthController::class, 'showChangePasswordForm'])->middleware('auth')->name('change-password');
Route::post('/change-password', [App\Http\Controllers\AuthController::class, 'changePassword'])->middleware('auth');

// Student dashboard
Route::middleware(['auth', 'role:student'])->prefix('student')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Student\DashboardController::class, 'index']);
    Route::get('/application', [App\Http\Controllers\Student\ApplicationController::class, 'show']);
    Route::get('/application/create', [App\Http\Controllers\Student\ApplicationController::class, 'create']);
    Route::post('/application/create', [App\Http\Controllers\Student\ApplicationController::class, 'store']);
    Route::post('/application/submit', [App\Http\Controllers\Student\ApplicationController::class, 'submitApplication']);
    Route::get('/documents', [App\Http\Controllers\Student\DocumentController::class, 'index'])->name('student.documents');
    Route::post('/documents/upload', [App\Http\Controllers\Student\DocumentController::class, 'upload']);
    Route::get('/messages', [App\Http\Controllers\Student\MessageController::class, 'index']);
    Route::post('/messages/send', [App\Http\Controllers\Student\MessageController::class, 'send']);
});

// Admin dashboard
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, 'index']);
    Route::get('/applications', [App\Http\Controllers\Admin\ApplicationController::class, 'index']);
    Route::put('/applications/{id}/status', [App\Http\Controllers\Admin\ApplicationController::class, 'updateStatus']);
    Route::get('/documents', [App\Http\Controllers\Admin\DocumentController::class, 'index']);
    Route::get('/documents/{id}/view', [App\Http\Controllers\Admin\DocumentController::class, 'viewDocument']);
    Route::get('/documents/{id}/serve', [App\Http\Controllers\Admin\DocumentController::class, 'serveDocument']);
    Route::get('/documents/{id}/download', [App\Http\Controllers\Admin\DocumentController::class, 'downloadDocument']);
    Route::put('/documents/{id}/validate', [App\Http\Controllers\Admin\DocumentController::class, 'validateDocument']);
    Route::get('/messages', [App\Http\Controllers\Admin\MessageController::class, 'index']);
    Route::post('/messages/send', [App\Http\Controllers\Admin\MessageController::class, 'send']);
    Route::get('/reports', [App\Http\Controllers\Admin\ReportController::class, 'index']);


    // User Management
    Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users.index');
    Route::post('/users/{user}/approve', [App\Http\Controllers\Admin\UserController::class, 'approve'])->name('admin.users.approve');
    Route::post('/users/{user}/revoke', [App\Http\Controllers\Admin\UserController::class, 'revoke'])->name('admin.users.revoke');
    Route::post('/users/{user}/toggle-approval', [App\Http\Controllers\Admin\UserController::class, 'toggleApproval'])->name('admin.users.toggle-approval');
    Route::post('/users/{user}/reset-password', [App\Http\Controllers\Admin\UserController::class, 'resetPassword'])->name('admin.users.reset-password');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead']);
    Route::get('/chatbot', [App\Http\Controllers\ChatbotController::class, 'index'])->name('chatbot.index');
    Route::post('/chatbot/send', [App\Http\Controllers\ChatbotController::class, 'sendMessage'])->name('chatbot.send');
});
