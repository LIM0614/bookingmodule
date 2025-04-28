<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    // 指定表名
    protected $table = 'admins';

    // 可批量赋值的字段（mass assignable）
    protected $fillable = [
        'email',
        'password',
        // 其他字段，如果你的admins表有更多栏位可以加
    ];

    // 隐藏字段（不会在数组或JSON中暴露）
    protected $hidden = [
        'password',
        'remember_token', // 如果有用Laravel remember me功能
    ];

    // 自动转换字段类型
    protected $casts = [
        'email_verified_at' => 'datetime', // 如果有email验证时间
    ];
}
