<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Store account mobiles as +639XXXXXXXXX and allow each number on one account only.
 * PostgreSQL unique constraints still allow more than one NULL, so Google accounts
 * can exist before a number is added.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS ck_users_phone');

        DB::statement(<<<'SQL'
            UPDATE users
            SET phone = '+63' || substring(regexp_replace(phone, '\D', '', 'g') from 2)
            WHERE phone IS NOT NULL
              AND regexp_replace(phone, '\D', '', 'g') ~ '^09[0-9]{9}$'
        SQL);

        DB::statement(<<<'SQL'
            UPDATE users
            SET phone = '+' || regexp_replace(phone, '\D', '', 'g')
            WHERE phone IS NOT NULL
              AND regexp_replace(phone, '\D', '', 'g') ~ '^639[0-9]{9}$'
              AND phone !~ '^\+639[0-9]{9}$'
        SQL);

        DB::statement("ALTER TABLE users ADD CONSTRAINT ck_users_phone CHECK (phone IS NULL OR phone ~ '^\\+639[0-9]{9}$')");
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_phone_unique');
        DB::statement('ALTER TABLE users ADD CONSTRAINT users_phone_unique UNIQUE (phone)');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_phone_unique');
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS ck_users_phone');
        DB::statement(<<<'SQL'
            UPDATE users
            SET phone = '0' || substring(phone from 4)
            WHERE phone ~ '^\+639[0-9]{9}$'
        SQL);
        DB::statement("ALTER TABLE users ADD CONSTRAINT ck_users_phone CHECK (phone IS NULL OR phone ~ '^09[0-9]{9}$')");
    }
};
