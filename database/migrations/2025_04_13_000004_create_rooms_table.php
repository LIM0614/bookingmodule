<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 房型表
        Schema::create('rooms', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->integer('capacity');
            $table->timestamps();
        });

        // 具体房间单元表：主键是 unit_number
        Schema::create('room_units', function (Blueprint $table): void {
            // 用 unit_number 做主键
            $table->string('unit_number')->primary();
            // 外键连到房型
            $table->foreignId('room_id')
                ->constrained('rooms')
                ->cascadeOnDelete();
            // 状态
            $table->enum('status', ['available', 'booked'])
                ->default('available');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_units');
        Schema::dropIfExists('rooms');
    }
};
