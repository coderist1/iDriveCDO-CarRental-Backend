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
        Schema::create('fuel_records', function (Blueprint $table) {
            $table->id('fuel_record_id');

            $table->foreignId('vehicle_id')
                ->constrained('vehicles', 'vehicle_id')
                ->cascadeOnDelete();

            $table->string('fuel_type');
            $table->decimal('quantity', 8, 2)->nullable();
            $table->decimal('fuel_cost', 10, 2)->nullable();
            $table->date('fuel_date');
            $table->decimal('mileage', 10, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_records');
    }
};
