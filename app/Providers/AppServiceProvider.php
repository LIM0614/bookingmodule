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
        // 先绑定真实服务
        $this->app->singleton(RealBookingService::class, RealBookingService::class);

        // 再用 Proxy 包裹真实服务，注入到接口
        $this->app->bind(BookingServiceInterface::class, function ($app) {
            $real = $app->make(RealBookingService::class);
            return new BookingServiceProxy($real);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
