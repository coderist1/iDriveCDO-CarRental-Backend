<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Cashless payments keep the GCash or Instapay receipt the renter uploaded. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'receipt')) {
                $table->text('receipt')->nullable();
            }
        });

        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS ck_payments_receipt');
        DB::statement(<<<'SQL'
            ALTER TABLE payments ADD CONSTRAINT ck_payments_receipt CHECK (
              receipt IS NULL OR receipt ~* '^data:image/(jpeg|jpg|png|webp);base64,'
            )
        SQL);

        DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS ck_payments_brand');
        DB::statement(<<<'SQL'
            ALTER TABLE payments ADD CONSTRAINT ck_payments_brand CHECK (
              (payment_method = 'cash' AND brand = 'Cash')
              OR (payment_method = 'cashless' AND brand IN ('GCash', 'Maya', 'GrabPay', 'Instapay'))
              OR payment_method = 'card'
            )
        SQL);
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS ck_payments_receipt');
            DB::statement('ALTER TABLE payments DROP CONSTRAINT IF EXISTS ck_payments_brand');
            DB::statement(<<<'SQL'
                ALTER TABLE payments ADD CONSTRAINT ck_payments_brand CHECK (
                  (payment_method = 'cash' AND brand = 'Cash')
                  OR (payment_method = 'cashless' AND brand IN ('GCash', 'Maya', 'GrabPay'))
                  OR payment_method = 'card'
                )
            SQL);
        }

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'receipt')) {
                $table->dropColumn('receipt');
            }
        });
    }
};
