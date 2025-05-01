<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\AdminBookingController;


Auth::routes();

Route::get('/bookings/xml', function () {
    $path = storage_path('app/public/bookings.xml');

    if (file_exists($path)) {
        return response()->file($path);
    } else {
        return response()->json(['error' => 'Booking XML not found'], 404);
    }
});

Route::get('/transform-xml', [BookingController::class, 'transformXmlToXhtml']);

Route::get('/', function (Request $request) {
    if (auth('admin')->check()) {
        return redirect('/admin/bookings');
    } elseif (auth('web')->check()) {
        return redirect('/bookings');
    }
    return redirect()->route('login');
});


Route::post('/login', [LoginController::class, 'login'])
    ->middleware('guest:web')
    ->name('login.custom');


Route::middleware('auth:web')->group(function () {

    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');

    Route::get('/bookings/myBookings', [BookingController::class, 'myBookings'])->name('bookings.my');

    Route::get('/bookings/create/{roomTypeId}', [BookingController::class, 'create'])->name('bookings.create');

    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');

    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');

    Route::get('/bookings/{booking}/edit', [BookingController::class, 'edit'])->name('bookings.edit');

    Route::put('/bookings/{booking}/update', [BookingController::class, 'update'])->name('bookings.update');

    Route::get('/bookings/{booking}/cancel', [BookingController::class, 'showCancel'])->name('bookings.cancel.confirm');

    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

});



Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');
});

Route::middleware('auth:admin')->prefix('admin')->name('admin.')->group(function () {

    Route::get('/bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [AdminBookingController::class, 'show'])->name('bookings.show');

    Route::post('/bookings/{booking}/force-cancel', [AdminBookingController::class, 'forceCancel'])->name('bookings.forceCancel');
    Route::post('/bookings/{booking}/checkin', [AdminBookingController::class, 'checkIn'])->name('bookings.checkin');
    Route::post('/bookings/{booking}/checkout', [AdminBookingController::class, 'checkOut'])->name('bookings.checkout');
});
