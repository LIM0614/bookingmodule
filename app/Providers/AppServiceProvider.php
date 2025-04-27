<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\BookingServiceInterface;
use App\Services\RealBookingService;
use App\Services\BookingServiceProxy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // 1) Register the real implementation so the proxy can resolve it:
        $this->app->singleton(
            RealBookingService::class,
            RealBookingService::class
        );

        // 2) Make BookingServiceInterface resolve to the proxy:
        $this->app->bind(
            BookingServiceInterface::class,
            function ($app) {
                $real = $app->make(RealBookingService::class);
                return new BookingServiceProxy($real);
            }
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
