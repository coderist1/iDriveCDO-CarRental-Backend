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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id('booking_id');

            $table->foreignId('driver_details_id')
                ->nullable()
                ->constrained('driver_details', 'driver_details_id')
                ->nullOnDelete();

            $table->foreignId('vehicle_id')
                ->constrained('vehicles', 'vehicle_id')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->time('pickup_time');
            $table->date('pickup_date');

            $table->time('return_time')->nullable();
            $table->date('return_date')->nullable();

            $table->string('payment_method');
            $table->integer('number_of_passenger');

            $table->string('driver_option');

            $table->decimal('fuel_before_rent', 5, 2)->nullable();
            $table->decimal('fuel_upon_return', 5, 2)->nullable();

            $table->date('date_reserve');

            $table->string('booking_status')->default('Pending');

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
