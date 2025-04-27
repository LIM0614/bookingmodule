<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\AdminBookingController;
use Illuminate\Http\Request;

// 登录、注册、重设密码路由
Auth::routes();

// ✨ 改成 / 第一次访问行为
Route::get('/', function (Request $request) {
    if (auth()->check()) {
        // 如果已经登录，直接跳 bookings 页面
        return redirect('/bookings');
    }
    // 如果未登录，去 login 页面
    return redirect()->route('login');
});

// ✨ 登录后自定义跳转 (用 POST /login 后)
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login'])->name('login.custom');

// ✨ Session expired 页面 (optional)
Route::get('/session-expired', function () {
    return view('auth.session_expired');
})->name('session_expired');

// ✨ 登录保护区域
Route::middleware(['auth'])->group(function () {

    // 登录成功后到 bookings 页面
    Route::get('/bookings', [BookingController::class, 'index'])->name('home');

    Route::resource('bookings', BookingController::class)->except(['destroy']);

    Route::get('bookings/{booking}/cancel', [BookingController::class, 'showCancel'])
        ->name('bookings.cancel.confirm');

    Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel'])
        ->name('bookings.cancel');

    // Admin部分
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/{booking}', [AdminBookingController::class, 'show'])->name('bookings.show');
        Route::post('/bookings/{booking}/force-cancel', [AdminBookingController::class, 'forceCancel'])->name('bookings.forceCancel');
    });
});
