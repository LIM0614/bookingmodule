<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table): void {
            $table->id();

            // 1) Link to users
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // 2) Link to room types
            $table->foreignId('room_type_id')
                ->constrained('room_types')
                ->onDelete('cascade');

            // 3) Link to rooms
            $table->foreignId('room_id')
                ->constrained('rooms')
                ->onDelete('cascade');

            // 4) Guest Info
            $table->string('name');
            $table->string('ic_passport');
            $table->string('contact_number');
            $table->integer('number_guest');

            // 5) Dates and Status
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->string('status')->default('pending'); // pending, checkin, checkout, cancelled

            // 6) Computed Duration
            $table->integer('duration')->storedAs('DATEDIFF(check_out_date, check_in_date)');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
