<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('parking duration');
            $table->string('uid');
            $table->decimal('discount');
            $table->string('status')->default('wait');
            $table->decimal('price_per_hour');
            $table->foreignId('user_id')->constrained();
            $table->foreignId('car_id')->constrained();
//            $table->foreignId('parking_id')->constrained();
//            $table->foreignId('parking_slot_id')->constrained();
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }


};
