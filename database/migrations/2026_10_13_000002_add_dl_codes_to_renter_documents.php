<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** A driver's license on a booking is DL codes, license number, and expiration date. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_renter_documents', function (Blueprint $table) {
            if (! Schema::hasColumn('booking_renter_documents', 'dl_codes')) {
                $table->string('dl_codes', 40)->nullable();
            }
        });

        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE booking_renter_documents DROP CONSTRAINT IF EXISTS ck_renter_docs_kind');
        DB::statement(<<<'SQL'
            ALTER TABLE booking_renter_documents ADD CONSTRAINT ck_renter_docs_kind CHECK (
              (id_type IS NOT NULL AND id_number IS NOT NULL)
              OR (
                dl_codes IS NOT NULL AND btrim(dl_codes) <> ''
                AND license_no IS NOT NULL AND license_expiry IS NOT NULL
              )
              OR (
                license_name IS NOT NULL AND license_no IS NOT NULL AND license_expiry IS NOT NULL
                AND license_address IS NOT NULL AND emergency_phone IS NOT NULL AND license_photo IS NOT NULL
              )
            )
        SQL);
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE booking_renter_documents DROP CONSTRAINT IF EXISTS ck_renter_docs_kind');
            DB::statement(<<<'SQL'
                ALTER TABLE booking_renter_documents ADD CONSTRAINT ck_renter_docs_kind CHECK (
                  (id_type IS NOT NULL AND id_number IS NOT NULL)
                  OR (license_name IS NOT NULL AND license_no IS NOT NULL AND license_expiry IS NOT NULL
                      AND license_address IS NOT NULL AND emergency_phone IS NOT NULL AND license_photo IS NOT NULL)
                )
            SQL);
        }

        Schema::table('booking_renter_documents', function (Blueprint $table) {
            if (Schema::hasColumn('booking_renter_documents', 'dl_codes')) {
                $table->dropColumn('dl_codes');
            }
        });
    }
};
