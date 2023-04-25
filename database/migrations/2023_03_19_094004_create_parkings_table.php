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
        Schema::create('parkings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
//            $table->decimal('latitude',12,10);
//            $table->decimal('longitude',12,10);
            $table->point('location');
            $table->decimal('credit',12,3)->default(0);
            $table->integer('number_of_slots');
            $table->integer('busy_slots')->default(0);
            $table->boolean('has_free_slot')->default(true);
            $table->decimal('total_rate',3,2);
            $table->smallInteger('number_of_floors');
            $table->foreignId('supplier_id')->constrained();
            $table->timestamps();

        });
        Schema::table('reservations', function(Blueprint $table) {
            $table->foreignId('parking_id')->constrained('parkings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parkings');
    }
};
