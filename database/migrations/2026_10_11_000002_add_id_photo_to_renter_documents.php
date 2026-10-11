<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Chauffeur bookings keep the government ID photo so the desk can compare it with the typed details. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_renter_documents', function (Blueprint $table) {
            $table->text('id_photo')->nullable();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE booking_renter_documents ADD CONSTRAINT ck_renter_docs_id_photo CHECK (id_photo IS NULL OR id_photo ~* '^data:image/(jpeg|jpg|png|webp);base64,')");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE booking_renter_documents DROP CONSTRAINT IF EXISTS ck_renter_docs_id_photo');
        }

        Schema::table('booking_renter_documents', function (Blueprint $table) {
            $table->dropColumn('id_photo');
        });
    }
};
