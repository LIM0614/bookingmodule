<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\BookingServiceInterface;
use App\Services\RealBookingService;
use App\Services\BookingServiceProxy;
use App\Listeners\InvalidateOtherSessions;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Login;

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
        // 每次用户登录时，调用我们的 InvalidateOtherSessions
        Event::listen(
            Login::class,
            InvalidateOtherSessions::class
        );
    }
}
