<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\DB;

class InvalidateOtherSessions
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;

        // 删掉该用户在 sessions 表里，除当前会话之外的所有记录
        DB::table('sessions')
            ->where('user_id', $user->id)
            ->where('id', '<>', session()->getId())
            ->delete();
    }
}
