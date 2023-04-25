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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('plat_number',10);
            $table->string('model',55)->nullable();
            $table->string('color',55)->nullable();
//            $table->string('license_number');
            $table->string('replacement_pic')->nullable();
            $table->string('replacement_pic1')->nullable();
            $table->string('replacement_pic2')->nullable();
            $table->string('replacement_pic3')->nullable();
            $table->string('replacement_pic4')->nullable();
//            $table->unsignedBigInteger('user_id');
//            $table->foreign('user_id')->references('id')->on('users');
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
