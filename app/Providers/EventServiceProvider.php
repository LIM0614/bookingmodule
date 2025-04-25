<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Auth\Events\Login;
use App\Listeners\InvalidateOtherSessions;

class EventServiceProvider extends ServiceProvider
{
    /**
     * 事件与监听器映射
     *
     * @var array
     */
    protected $listen = [
            // 用户登录时触发，调用我们的监听器
        Login::class => [
            InvalidateOtherSessions::class,
        ],
    ];

    /**
     * 注册事件
     */
    public function boot(): void
    {
        parent::boot();
    }
}
