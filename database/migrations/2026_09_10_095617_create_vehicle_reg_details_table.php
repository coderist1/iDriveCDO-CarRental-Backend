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
        Schema::create('vehicle_reg_details', function (Blueprint $table) {
            $table->id('vehicle_reg_det_id');

            $table->foreignId('vehicle_id')
                ->constrained('vehicles', 'vehicle_id')
                ->onDelete('cascade');

            $table->string('plate_number', 20);
            $table->date('renewal_scheduled_day');
            $table->date('next_reg_renewal');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_reg_details');
    }
};
