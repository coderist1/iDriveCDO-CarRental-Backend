<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Chauffeur bookings keep the government ID expiry so the desk can check it against the ID photo. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_renter_documents', function (Blueprint $table) {
            $table->date('id_expiry')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('booking_renter_documents', function (Blueprint $table) {
            $table->dropColumn('id_expiry');
        });
    }
};
