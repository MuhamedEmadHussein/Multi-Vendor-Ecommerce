<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;


Route::prefix('admin')->name('admin.')->group(function() {
    Route::middleware(['guest:admin','PreventBackHistory'])->group(function() {
        Route::view('/login', 'back.pages.admin.auth.login')->name('login');
        Route::post('/login_handler', [AdminController::class,'handleLogin'])->name('login_handler');
        Route::view('/forget-password', 'back.pages.admin.auth.forget-password')->name('forget-password');
        Route::post('/password-reset', [AdminController::class, 'sendPasswordResetLink'])->name('password-reset');
        Route::get('/password/reset/{token}',[AdminController::class, 'resetPassword'])->name('password-reset.show');
        Route::post('/reset-password-handler', [AdminController::class, 'handleResetPassword'])->name('resetPasswordHandler');
    });

    Route::middleware(['auth:admin','PreventBackHistory'])->group(function() {
        Route::view('/home', 'back.pages.admin.home')->name('home');
        Route::post('/logout', [AdminController::class, 'handleLogout'])->name('logout');
    });
});