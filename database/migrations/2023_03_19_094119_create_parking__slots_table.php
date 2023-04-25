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
        Schema::create('parking_slots', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('row');
            $table->smallInteger('column');
            $table->smallInteger('floor')->default(1);
            $table->foreignId('parking_id')->constrained();
            $table->string('status')->default('free');
            $table->timestamps();
        });
        Schema::table('reservations', function(Blueprint $table) {
            $table->foreignId('parking_slot_id')->constrained('parking_slots');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parking__slots');
    }
};
