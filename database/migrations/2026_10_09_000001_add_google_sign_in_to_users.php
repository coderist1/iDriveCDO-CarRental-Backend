<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Google sign-in: link accounts by Google's stable subject id, and allow a missing phone
 * (Google does not share one; the renter adds it before their first booking).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id', 64)->nullable()->unique();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE users ALTER COLUMN phone DROP NOT NULL');
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS ck_users_phone');
            DB::statement("ALTER TABLE users ADD CONSTRAINT ck_users_phone CHECK (phone IS NULL OR phone ~ '^09[0-9]{9}$')");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS ck_users_phone');
            DB::statement("ALTER TABLE users ADD CONSTRAINT ck_users_phone CHECK (phone ~ '^09[0-9]{9}$')");
            DB::statement('ALTER TABLE users ALTER COLUMN phone SET NOT NULL');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('google_id');
        });
    }
};
