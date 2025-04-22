<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\AdminBookingController;

Auth::routes();

Route::middleware('auth')->group(function () {
    // Home
    Route::get('/', [BookingController::class, 'index'])
        ->name('home');

    // Resourceful booking routes (index, create, store, show, edit, update)
    Route::resource('bookings', BookingController::class)
        ->except(['destroy']);

    // 1) Show “Are you sure you want to cancel?” page
    Route::get('bookings/{booking}/cancel', [BookingController::class, 'showCancel'])
        ->name('bookings.cancel.confirm');

    // 2) Perform the cancellation
    Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel'])
        ->name('bookings.cancel');
});

Route::prefix('admin')
    ->middleware('auth')
    ->name('admin.')
    ->group(function () {
        Route::get('bookings', [AdminBookingController::class, 'index'])
            ->name('bookings.index');
        Route::get('bookings/{booking}', [AdminBookingController::class, 'show'])
            ->name('bookings.show');
        Route::post('bookings/{booking}/force-cancel', [AdminBookingController::class, 'forceCancel'])
            ->name('bookings.forceCancel');
    });
