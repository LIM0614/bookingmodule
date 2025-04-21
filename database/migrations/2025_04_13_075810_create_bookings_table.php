<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            // ➕ Add the columns first
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->string('ic_passport');
            $table->integer('contact_number');
            $table->integer('number_guest');
            $table->unsignedBigInteger('room_id');

            // ➕ THEN add foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('cascade');

            // Booking details
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->string('status')->default('pending');

            $table->integer('duration')
                ->storedAs('DATEDIFF(check_out_date, check_in_date)');

            $table->timestamps();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
