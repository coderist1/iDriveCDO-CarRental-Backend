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
            $table->id('vehicle_id');
            $table->string('plate_number', 20)->unique();
            $table->integer('mileage')->default(0);
            $table->string('brand', 100);
            $table->string('model', 100);
            $table->string('type', 50);
            $table->integer('capacity');
            $table->year('year_model');
            $table->year('year_purchased');
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
