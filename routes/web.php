<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\AdminController;

Route::get('/', [RegistrationController::class, 'index'])->name('registration.index');
Route::post('/register', [RegistrationController::class, 'register'])->name('registration.store');
Route::get('/register/join', [RegistrationController::class, 'index'])->name('registration.join'); // Alias for join link

Route::get('/admin/login', [AdminController::class, 'loginForm'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login.submit');

Route::middleware(['web'])->group(function () {
    Route::get('/admin', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/admin/approve', [AdminController::class, 'approve'])->name('admin.approve');
    Route::post('/admin/reject', [AdminController::class, 'reject'])->name('admin.reject');
    Route::post('/admin/send-emails', [AdminController::class, 'sendEmails'])->name('admin.send_emails');
    Route::get('/admin/export', [AdminController::class, 'export'])->name('admin.export');
});
