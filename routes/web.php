<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\AdminBookingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
| These routes are loaded by the RouteServiceProvider within a group
| which contains the "web" middleware group.
|
*/

// Laravel auth routes (login, register, etc.)
Auth::routes();

// 1) Home → show booking index to logged‑in user
Route::get('/', [BookingController::class, 'index'])
    ->middleware('auth')
    ->name('home');

// 2) All RESTful routes for customer bookings
Route::middleware('auth')->group(function () {
    // index, create, store, show, edit, update, destroy
    Route::resource('bookings', BookingController::class)
        ->except(['destroy']);

    // POST  bookings/{booking}/cancel  → BookingController@cancel
    Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel'])
        ->name('bookings.cancel');

    // GET   bookings/{booking}/cancel  → BookingController@showCancel
    Route::get('bookings/{booking}/cancel', [BookingController::class, 'showCancel'])
        ->name('bookings.cancel.confirm');
});

// 3) Admin routes (only for users with auth; you can add can:admin as needed)
Route::prefix('admin')
    ->name('admin.')
    ->middleware('auth')
    ->group(function () {
        // Admin: view all bookings
        Route::get('bookings', [AdminBookingController::class, 'index'])
            ->name('bookings.index');

        // Admin: view a specific booking
        Route::get('bookings/{booking}', [AdminBookingController::class, 'show'])
            ->name('bookings.show');

        // Admin: force‑cancel a booking
        Route::post('bookings/{booking}/force-cancel', [AdminBookingController::class, 'forceCancel'])
            ->name('bookings.forceCancel');
    });
