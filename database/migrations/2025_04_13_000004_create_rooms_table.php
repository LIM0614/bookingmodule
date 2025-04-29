<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 房型表
        Schema::create('room_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->integer('capacity');
            $table->decimal('price_per_night', 8, 2);
            $table->text('description')->nullable();
            $table->json('amenities')->nullable(); // like ["WiFi", "TV"]
            $table->string('image')->nullable();    // like ["room1.jpg", "room2.jpg"]
            $table->timestamps();
        });


        Schema::create('rooms', function (Blueprint $table): void {
            $table->id();

            $table->string('room_number')->unique();

            $table->foreignId('room_type_id')
                ->constrained('room_types')
                ->OnDelete('cascade');

            $table->enum('status', ['available', 'occupied', 'maintenance', 'cleaning'])->default('available');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
        Schema::dropIfExists('room_types');
    }
};
