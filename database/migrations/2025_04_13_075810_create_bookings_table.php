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

            // 2) Guest info
            $table->string('name');
            $table->string('ic_passport');
            $table->string('contact_number');
            $table->integer('number_guest');

            // 3) Room type
            $table->foreignId('room_id')
                ->constrained('rooms')
                ->cascadeOnDelete();

            // 4) Concrete unit (no ->after())
            $table->string('room_unit_number')
                ->nullable();

            // 5) Dates & status
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->string('status')->default('pending');

            // 6) Computed duration
            $table->integer('duration')
                ->storedAs('DATEDIFF(check_out_date, check_in_date)');

            $table->timestamps();

            // 7) Now apply the FK constraint
            $table->foreign('room_unit_number')
                ->references('unit_number')
                ->on('room_units')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
