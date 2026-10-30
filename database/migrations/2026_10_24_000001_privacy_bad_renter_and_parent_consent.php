<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'birthdate')) {
                $table->date('birthdate')->nullable();
            }
            if (! Schema::hasColumn('users', 'privacy_accepted_at')) {
                $table->timestampTz('privacy_accepted_at')->nullable();
            }
            if (! Schema::hasColumn('users', 'is_bad_renter')) {
                $table->boolean('is_bad_renter')->default(false);
            }
            if (! Schema::hasColumn('users', 'bad_renter_reason')) {
                $table->string('bad_renter_reason', 400)->nullable();
            }
            if (! Schema::hasColumn('users', 'bad_renter_labeled_by')) {
                $table->unsignedBigInteger('bad_renter_labeled_by')->nullable();
            }
            if (! Schema::hasColumn('users', 'bad_renter_labeled_at')) {
                $table->timestampTz('bad_renter_labeled_at')->nullable();
            }
        });

        Schema::table('booking_renter_documents', function (Blueprint $table) {
            if (! Schema::hasColumn('booking_renter_documents', 'birthdate')) {
                $table->date('birthdate')->nullable();
            }
            if (! Schema::hasColumn('booking_renter_documents', 'parent_name')) {
                $table->string('parent_name', 80)->nullable();
            }
            if (! Schema::hasColumn('booking_renter_documents', 'parent_phone')) {
                $table->string('parent_phone', 15)->nullable();
            }
            if (! Schema::hasColumn('booking_renter_documents', 'parent_id_photo')) {
                $table->text('parent_id_photo')->nullable();
            }
            if (! Schema::hasColumn('booking_renter_documents', 'parent_consent_at')) {
                $table->timestampTz('parent_consent_at')->nullable();
            }
            if (! Schema::hasColumn('booking_renter_documents', 'privacy_accepted_at')) {
                $table->timestampTz('privacy_accepted_at')->nullable();
            }
        });

        if (! Schema::hasTable('data_requests')) {
            Schema::create('data_requests', function (Blueprint $table) {
                $table->bigIncrements('request_id');
                $table->unsignedBigInteger('user_id');
                $table->string('type', 12);
                $table->string('status', 12)->default('pending');
                $table->json('payload')->nullable();
                $table->unsignedBigInteger('handled_by')->nullable();
                $table->timestampTz('fulfilled_at')->nullable();
                $table->timestamps();
                $table->foreign('user_id')->references('user_id')->on('users')->cascadeOnDelete();
            });
        }

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE message_threads DROP CONSTRAINT IF EXISTS ck_threads_kind');
            DB::statement("ALTER TABLE message_threads ADD CONSTRAINT ck_threads_kind CHECK (kind IN ('booking', 'contact', 'return', 'notice'))");
            DB::statement("ALTER TABLE booking_renter_documents DROP CONSTRAINT IF EXISTS ck_renter_docs_parent_phone");
            DB::statement("ALTER TABLE booking_renter_documents ADD CONSTRAINT ck_renter_docs_parent_phone CHECK (parent_phone IS NULL OR parent_phone ~ '^09[0-9]{9}$')");
            DB::statement("ALTER TABLE booking_renter_documents DROP CONSTRAINT IF EXISTS ck_renter_docs_parent_photo");
            DB::statement("ALTER TABLE booking_renter_documents ADD CONSTRAINT ck_renter_docs_parent_photo CHECK (parent_id_photo IS NULL OR parent_id_photo ~* '^data:image/(jpeg|jpg|png|webp);base64,')");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('data_requests');
        Schema::table('users', function (Blueprint $table) {
            foreach (['birthdate', 'privacy_accepted_at', 'is_bad_renter', 'bad_renter_reason', 'bad_renter_labeled_by', 'bad_renter_labeled_at'] as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
        Schema::table('booking_renter_documents', function (Blueprint $table) {
            foreach (['birthdate', 'parent_name', 'parent_phone', 'parent_id_photo', 'parent_consent_at', 'privacy_accepted_at'] as $col) {
                if (Schema::hasColumn('booking_renter_documents', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE message_threads DROP CONSTRAINT IF EXISTS ck_threads_kind');
            DB::statement("ALTER TABLE message_threads ADD CONSTRAINT ck_threads_kind CHECK (kind IN ('booking', 'contact', 'return'))");
        }
    }
};
