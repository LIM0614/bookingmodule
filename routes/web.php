<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\AdminBookingController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\AdminLoginController;
use Illuminate\Http\Request;

// 🔥 登录/注册/密码重设
Auth::routes();

// 🔥 顾客访问首页 `/`
Route::get('/', function (Request $request) {
    if (auth('web')->check()) {
        return redirect('/bookings');
    }
    return redirect()->route('login');
});

// 🔥 顾客处理登录 (POST /login)
Route::post('/login', [LoginController::class, 'login'])
    ->middleware('guest:web')
    ->name('login.custom');

// 🔥 顾客功能保护区
Route::middleware(['auth:web'])->group(function () {
    // 登录后跳到 Bookings
    Route::get('/bookings', [BookingController::class, 'index'])->name('home');

    // Bookings 资源路由 (除了 destroy)
    Route::resource('bookings', BookingController::class)->except(['destroy']);

    // 取消Booking相关
    Route::get('bookings/{booking}/cancel', [BookingController::class, 'showCancel'])
        ->name('bookings.cancel.confirm');
    Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel'])
        ->name('bookings.cancel');
});

// 🔥 Admin登录页面和处理
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'login'])
        ->middleware('guest:admin')
        ->name('login.submit');
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');
});

// 🔥 Admin功能保护区
Route::middleware(['auth:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin 查看所有 Bookings
    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [AdminBookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/force-cancel', [AdminBookingController::class, 'forceCancel'])->name('bookings.forceCancel');
});
