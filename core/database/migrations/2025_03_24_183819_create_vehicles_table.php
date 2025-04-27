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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->integer('service_id');
            $table->string('model');
            $table->string('number');
            $table->string('color');
            $table->string('insurance')->nullable();
            $table->string('rc')->nullable();
            $table->string('image')->nullable();
            $table->string('polution_certificate')->nullable();
            $table->boolean('status')->default(0);
            $table->boolean('is_occupied')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
