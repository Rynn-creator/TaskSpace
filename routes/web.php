<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminPasswordResetRequestController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\PasswordResetRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserPresenceController;
use Illuminate\Support\Facades\Route;

// Guest Routes (Belum Login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::get('/password/forgot', [PasswordResetRequestController::class, 'create'])->name('password.request');
Route::post('/password/forgot', [PasswordResetRequestController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('password.request.store');
Route::get('/password/reset/{token}', [PasswordResetRequestController::class, 'edit'])->name('password.reset');
Route::post('/password/reset', [PasswordResetRequestController::class, 'update'])->name('password.update');

Route::get('/', [LandingPageController::class, 'index'])->middleware('active')->name('tasks.index');

// Authenticated Routes (Harus Login Dulu)
Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/photo', [ProfileController::class, 'destroyPhoto'])->name('profile.photo.destroy');
    Route::get('/session/heartbeat', UserPresenceController::class)->name('session.heartbeat');
    Route::get('/admin/user-presence', [TaskController::class, 'userPresence'])->name('admin.user-presence');
    Route::get('/admin', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/statistics', [DashboardController::class, 'statistics'])->name('admin.statistics');
    Route::get('/admin/live/dashboard', [DashboardController::class, 'liveDashboard'])->name('admin.live.dashboard');
    Route::get('/admin/live/statistics', [DashboardController::class, 'liveStatistics'])->name('admin.live.statistics');
    Route::get('/admin/activity-analytics', [DashboardController::class, 'activityAnalytics'])->name('admin.activity-analytics');
    Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/{user}', [AdminUserController::class, 'show'])->name('admin.users.show');
    Route::post('/admin/users/{user}/password-reset', [AdminUserController::class, 'sendResetLink'])
        ->middleware('throttle:5,1')
        ->name('admin.users.password-reset');
    Route::patch('/admin/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('admin.users.role');
    Route::patch('/admin/users/{user}/suspension', [AdminUserController::class, 'toggleSuspension'])->name('admin.users.suspension');
    Route::delete('/admin/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
    Route::get('/admin/password-reset-requests', [AdminPasswordResetRequestController::class, 'index'])->name('admin.password-requests.index');
    Route::post('/admin/password-reset-requests/{passwordResetRequest}/send', [AdminPasswordResetRequestController::class, 'sendResetLink'])->name('admin.password-requests.send');

    Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback.index');
    Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');
    Route::get('/admin/feedback', [FeedbackController::class, 'adminIndex'])->name('admin.feedback.index');
    Route::post('/admin/feedback/{feedback}/reply', [FeedbackController::class, 'reply'])->name('admin.feedback.reply');

    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::patch('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::get('/task-attachments/{attachment}/download', [TaskController::class, 'downloadAttachment'])->name('tasks.attachments.download');
    Route::delete('/task-attachments/{attachment}', [TaskController::class, 'destroyAttachment'])->name('tasks.attachments.destroy');
    Route::patch('/notifications/{notification}/read', [TaskController::class, 'markReminderRead'])->name('notifications.read');
    Route::delete('/tasks-clear-completed', [TaskController::class, 'clearCompleted'])->name('tasks.clearCompleted');
});