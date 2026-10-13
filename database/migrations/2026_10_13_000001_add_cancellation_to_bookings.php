<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** A customer cancellation keeps the reason and the time, and only a pending booking can be cancelled. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('cancel_reason', 240)->nullable();
            $table->timestampTz('cancelled_at')->nullable();
        });

        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement(<<<'SQL'
CREATE OR REPLACE FUNCTION public.fn_bookings_before_update()
 RETURNS trigger
 LANGUAGE plpgsql
 SET search_path TO 'public'
AS $function$
BEGIN
  IF NEW.status <> OLD.status AND NOT (
       (OLD.status = 'pending'          AND NEW.status IN ('confirmed','rejected','cancelled'))
    OR (OLD.status = 'confirmed'        AND NEW.status IN ('ongoing','return_requested'))
    OR (OLD.status = 'ongoing'          AND NEW.status IN ('completed','return_requested'))
    OR (OLD.status = 'return_requested' AND NEW.status IN ('completed','ongoing'))
  ) THEN
    RAISE EXCEPTION 'That status change is not allowed.';
  END IF;

  IF NEW.status = 'cancelled' AND OLD.status <> 'cancelled' THEN
    IF NEW.cancel_reason IS NULL OR btrim(NEW.cancel_reason) = '' OR NEW.cancelled_at IS NULL THEN
      RAISE EXCEPTION 'A cancellation needs a reason and a time.';
    END IF;
  END IF;

  RETURN NEW;
END $function$
SQL);
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement(<<<'SQL'
CREATE OR REPLACE FUNCTION public.fn_bookings_before_update()
 RETURNS trigger
 LANGUAGE plpgsql
 SET search_path TO 'public'
AS $function$
BEGIN
  IF NEW.status <> OLD.status AND NOT (
       (OLD.status = 'pending'          AND NEW.status IN ('confirmed','rejected','cancelled'))
    OR (OLD.status = 'confirmed'        AND NEW.status IN ('ongoing','cancelled','return_requested'))
    OR (OLD.status = 'ongoing'          AND NEW.status IN ('completed','return_requested'))
    OR (OLD.status = 'return_requested' AND NEW.status IN ('completed','ongoing'))
  ) THEN
    RAISE EXCEPTION 'That status change is not allowed.';
  END IF;
  RETURN NEW;
END $function$
SQL);
        }

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['cancel_reason', 'cancelled_at']);
        });
    }
};
