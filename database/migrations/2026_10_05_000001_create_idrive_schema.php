<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tables created by this migration, in dependency order.
     *
     * @var list<string>
     */
    private array $tables = [
        'users', 'user_sessions', 'login_lockouts', 'locations', 'addons',
        'vehicles', 'vehicle_features', 'drivers', 'bookings', 'booking_addons',
        'booking_renter_documents', 'payments', 'ratings', 'message_threads',
        'thread_messages', 'vehicle_registrations', 'telemetry_readings',
        'maintenance_predictions', 'maintenances', 'fuel_records', 'audit_logs',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            return;
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::unprepared(file_get_contents(database_path('idrive_schema.sql')));

            return;
        }

        $this->createPortableSchema();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // The PostgreSQL schema is the live Supabase database; rolling back must never drop it.
        if (DB::getDriverName() === 'pgsql') {
            return;
        }

        Schema::disableForeignKeyConstraints();

        foreach (array_reverse($this->tables) as $table) {
            Schema::dropIfExists($table);
        }

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Same tables and columns as database/idrive_schema.sql, for SQLite (tests).
     * CHECK constraints, triggers, views and the overlap exclusion are PostgreSQL-only.
     */
    private function createPortableSchema(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('code', 40)->unique();
            $table->string('email', 120)->unique();
            $table->string('password_hash');
            $table->string('password_salt', 64)->nullable();
            $table->string('role', 10)->default('customer');
            $table->string('first_name', 40);
            $table->string('last_name', 40);
            $table->string('phone', 15);
            $table->string('address', 120)->nullable();
            $table->string('department', 60)->nullable();
            $table->string('license_no', 30)->nullable();
            $table->date('license_expiry')->nullable();
            $table->text('avatar')->nullable();
            $table->string('status', 10)->default('active');
            $table->timestampTz('age_confirmed_at')->nullable();
            $table->timestampTz('terms_accepted_at')->nullable();
            $table->timestampTz('password_changed_at')->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->timestampTz('email_verified_at')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->index(['role', 'status']);
        });

        Schema::create('user_sessions', function (Blueprint $table) {
            $table->char('session_id', 32)->primary();
            $table->foreignId('user_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->string('role', 10);
            $table->string('csrf_token', 64)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestampTz('issued_at')->useCurrent();
            $table->timestampTz('last_seen_at')->useCurrent();
            $table->timestampTz('expires_at')->index();
            $table->timestampTz('revoked_at')->nullable();
        });

        Schema::create('login_lockouts', function (Blueprint $table) {
            $table->char('email_hash', 64)->primary();
            $table->smallInteger('failed_count')->default(0);
            $table->timestampTz('last_failed_at')->nullable();
            $table->timestampTz('locked_until')->nullable();
        });

        Schema::create('locations', function (Blueprint $table) {
            $table->smallIncrements('location_id');
            $table->string('name', 80)->unique();
            $table->boolean('is_active')->default(true);
            $table->smallInteger('sort_order')->default(0);
        });

        Schema::create('addons', function (Blueprint $table) {
            $table->smallIncrements('addon_id');
            $table->string('code', 20)->unique();
            $table->string('name', 60);
            $table->decimal('daily_rate', 10, 2);
            $table->boolean('is_active')->default(true);
        });

        Schema::create('vehicles', function (Blueprint $table) {
            $table->id('vehicle_id');
            $table->string('code', 40)->unique();
            $table->string('name', 60);
            $table->string('brand', 30)->index();
            $table->string('model', 30);
            $table->smallInteger('year_model');
            $table->smallInteger('year_purchased')->nullable();
            $table->string('type', 10);
            $table->string('transmission', 10);
            $table->string('fuel', 10);
            $table->smallInteger('capacity')->default(5);
            $table->smallInteger('luggage')->default(2);
            $table->integer('mileage')->default(0);
            $table->decimal('daily_rate', 10, 2);
            $table->string('plate_number', 12)->unique();
            $table->string('image', 300)->nullable();
            $table->string('description', 400)->nullable();
            $table->string('status', 12)->default('available');
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->softDeletesTz();
            $table->index(['status', 'type']);
        });

        Schema::create('vehicle_features', function (Blueprint $table) {
            $table->id('vehicle_feature_id');
            $table->foreignId('vehicle_id')->constrained('vehicles', 'vehicle_id')->cascadeOnDelete();
            $table->string('feature', 40);
            $table->smallInteger('sort_order')->default(0);
            $table->unique(['vehicle_id', 'feature']);
        });

        Schema::create('drivers', function (Blueprint $table) {
            $table->id('driver_id');
            $table->string('code', 40)->unique();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users', 'user_id')->nullOnDelete();
            $table->string('full_name', 80);
            $table->string('driver_license', 30)->unique();
            $table->string('type_driver_license', 30)->default('Professional');
            $table->date('license_expiry');
            $table->string('phone', 15)->nullable();
            $table->string('status', 10)->default('active');
            $table->string('duty_status', 10)->default('regular');
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->softDeletesTz();
            $table->index(['status', 'duty_status']);
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id('booking_id');
            $table->string('code', 40)->unique();
            $table->string('ref', 20)->unique();
            $table->foreignId('user_id')->constrained('users', 'user_id');
            $table->foreignId('vehicle_id')->constrained('vehicles', 'vehicle_id');
            $table->foreignId('driver_id')->nullable()->constrained('drivers', 'driver_id');
            $table->foreignId('created_by')->nullable()->constrained('users', 'user_id');
            $table->date('start_date');
            $table->date('end_date');
            $table->time('pickup_time')->default('09:00:00');
            $table->time('return_time')->default('09:00:00');
            $table->smallInteger('days');
            $table->unsignedSmallInteger('pickup_location_id');
            $table->unsignedSmallInteger('dropoff_location_id');
            $table->smallInteger('number_of_passengers')->default(1);
            $table->string('drive_mode', 10)->default('self');
            $table->string('driver_option', 12)->nullable()
                ->storedAs("CASE WHEN drive_mode = 'chauffeur' THEN 'Chauffeur' ELSE 'Self-drive' END");
            $table->string('fuel_before_rent', 10)->default('Full');
            $table->string('fuel_upon_return', 10)->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('extras', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('status', 20)->default('pending');
            $table->string('payment_status', 10)->default('unpaid');
            $table->string('payment_method', 10)->nullable();
            $table->string('notes', 240)->nullable();
            $table->string('return_notes', 240)->nullable();
            $table->timestampTz('date_reserve')->useCurrent();
            $table->timestampTz('started_at')->nullable();
            $table->foreignId('started_by')->nullable()->constrained('users', 'user_id');
            $table->timestampTz('return_requested_at')->nullable();
            $table->foreignId('return_requested_by')->nullable()->constrained('users', 'user_id');
            $table->timestampTz('returned_at')->nullable();
            $table->foreignId('returned_by')->nullable()->constrained('users', 'user_id');
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->foreign('pickup_location_id')->references('location_id')->on('locations');
            $table->foreign('dropoff_location_id')->references('location_id')->on('locations');
            $table->index(['vehicle_id', 'start_date', 'end_date']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('booking_addons', function (Blueprint $table) {
            $table->foreignId('booking_id')->constrained('bookings', 'booking_id')->cascadeOnDelete();
            $table->unsignedSmallInteger('addon_id');
            $table->decimal('daily_rate', 10, 2);
            $table->smallInteger('days');
            $table->decimal('line_total', 10, 2)->nullable()->storedAs('daily_rate * days');
            $table->primary(['booking_id', 'addon_id']);
            $table->foreign('addon_id')->references('addon_id')->on('addons')->restrictOnDelete();
        });

        Schema::create('booking_renter_documents', function (Blueprint $table) {
            $table->foreignId('booking_id')->primary()->constrained('bookings', 'booking_id')->cascadeOnDelete();
            $table->string('license_name', 60)->nullable();
            $table->string('license_no', 20)->nullable();
            $table->date('license_expiry')->nullable();
            $table->string('license_address', 120)->nullable();
            $table->string('emergency_phone', 15)->nullable();
            $table->text('license_photo')->nullable();
            $table->string('id_type', 20)->nullable();
            $table->string('id_number', 30)->nullable();
            $table->timestampTz('created_at')->useCurrent();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id('payment_id');
            $table->string('code', 40)->unique();
            $table->foreignId('booking_id')->constrained('bookings', 'booking_id')->restrictOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method', 10);
            $table->string('brand', 20);
            $table->string('account_last4', 4)->nullable();
            $table->string('holder', 80)->nullable();
            $table->string('reference_number', 40)->unique();
            $table->string('payment_status', 10)->default('paid');
            $table->timestampTz('payment_date')->useCurrent();
            $table->foreignId('recorded_by')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->timestampTz('created_at')->useCurrent();
        });

        Schema::create('ratings', function (Blueprint $table) {
            $table->id('rating_id');
            $table->string('code', 40)->unique();
            $table->foreignId('booking_id')->unique()->constrained('bookings', 'booking_id')->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained('vehicles', 'vehicle_id')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->smallInteger('stars');
            $table->string('comment', 280)->nullable();
            $table->timestampTz('created_at')->useCurrent();
        });

        Schema::create('message_threads', function (Blueprint $table) {
            $table->id('thread_id');
            $table->string('code', 40)->unique();
            $table->string('kind', 10);
            $table->string('topic', 80);
            $table->foreignId('booking_id')->nullable()->unique()->constrained('bookings', 'booking_id')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->string('customer_name', 80);
            $table->string('customer_email', 120)->nullable()->index();
            $table->string('customer_phone', 15)->nullable();
            $table->string('status', 10)->default('open');
            $table->boolean('unread_staff')->default(true);
            $table->boolean('unread_customer')->default(false);
            $table->string('legacy_id', 40)->nullable()->unique();
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
        });

        Schema::create('thread_messages', function (Blueprint $table) {
            $table->id('message_id');
            $table->string('code', 40)->unique();
            $table->foreignId('thread_id')->constrained('message_threads', 'thread_id')->cascadeOnDelete();
            $table->string('from_role', 10);
            $table->foreignId('from_user_id')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->string('from_name', 80);
            $table->string('body', 500);
            $table->timestampTz('sent_at')->useCurrent();
        });

        Schema::create('vehicle_registrations', function (Blueprint $table) {
            $table->id('registration_id');
            $table->string('code', 40)->unique();
            $table->foreignId('vehicle_id')->unique()->constrained('vehicles', 'vehicle_id')->cascadeOnDelete();
            $table->string('plate_number', 12);
            $table->smallInteger('renewal_scheduled_day')->nullable();
            $table->date('next_reg_renewal')->index();
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
        });

        Schema::create('telemetry_readings', function (Blueprint $table) {
            $table->id('telemetry_id');
            $table->foreignId('vehicle_id')->constrained('vehicles', 'vehicle_id')->cascadeOnDelete();
            $table->string('brand', 30);
            $table->timestamp('reading_time');
            $table->smallInteger('reading_hour')->nullable()
                ->storedAs("CAST(strftime('%H', reading_time) AS INTEGER)");
            $table->smallInteger('reading_day_of_week')->nullable()
                ->storedAs("(CAST(strftime('%w', reading_time) AS INTEGER) + 6) % 7");
            $table->decimal('odometer_reading', 10, 1);
            $table->decimal('engine_temp_c', 6, 2);
            $table->decimal('engine_rpm', 7, 1);
            $table->decimal('oil_pressure_psi', 6, 2);
            $table->decimal('coolant_temp_c', 6, 2);
            $table->decimal('fuel_level_percent', 5, 2);
            $table->decimal('fuel_consumption_lph', 6, 2);
            $table->decimal('engine_load_percent', 5, 2);
            $table->decimal('throttle_pos_percent', 5, 2);
            $table->decimal('air_flow_rate_gps', 7, 2);
            $table->decimal('exhaust_gas_temp_c', 6, 2);
            $table->decimal('vibration_level', 5, 2);
            $table->decimal('engine_hours', 9, 1);
            $table->decimal('brake_fluid_level_psi', 7, 2);
            $table->decimal('brake_pad_wear_mm', 5, 2);
            $table->decimal('brake_temp_c', 6, 2);
            $table->smallInteger('abs_fault_indicator')->default(0);
            $table->decimal('brake_pedal_pos_percent', 5, 2);
            $table->decimal('wheel_speed_fl_kph', 6, 2);
            $table->decimal('wheel_speed_fr_kph', 6, 2);
            $table->decimal('wheel_speed_rl_kph', 6, 2);
            $table->decimal('wheel_speed_rr_kph', 6, 2);
            $table->decimal('battery_voltage_v', 5, 2);
            $table->decimal('battery_current_a', 6, 2);
            $table->decimal('battery_temp_c', 6, 2);
            $table->decimal('alternator_output_v', 5, 2);
            $table->decimal('battery_charge_percent', 5, 2);
            $table->decimal('battery_health_percent', 5, 2);
            $table->decimal('vehicle_speed_kph', 6, 2);
            $table->decimal('ambient_temp_c', 5, 2);
            $table->decimal('humidity_percent', 5, 2);
            $table->jsonb('extra_attributes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->timestampTz('created_at')->useCurrent();
            $table->index(['vehicle_id', 'created_at']);
        });

        Schema::create('maintenance_predictions', function (Blueprint $table) {
            $table->id('prediction_id');
            $table->foreignId('vehicle_id')->constrained('vehicles', 'vehicle_id')->cascadeOnDelete();
            $table->foreignId('telemetry_id')->nullable()->constrained('telemetry_readings', 'telemetry_id')->nullOnDelete();
            $table->string('target', 30)->default('failure_imminent');
            $table->smallInteger('prediction');
            $table->boolean('needs_maintenance');
            $table->decimal('probability', 7, 6)->nullable();
            $table->string('model_name', 60)->default('failure_imminent_rf');
            $table->foreignId('predicted_by')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->timestampTz('predicted_at')->useCurrent();
        });

        Schema::create('maintenances', function (Blueprint $table) {
            $table->id('maintenance_id');
            $table->string('code', 40)->unique();
            $table->foreignId('vehicle_id')->constrained('vehicles', 'vehicle_id')->cascadeOnDelete();
            $table->string('maintenance_type', 60);
            $table->date('scheduled_date')->index();
            $table->date('performed_at')->nullable();
            $table->boolean('finished')->default(false);
            $table->string('notes', 200)->nullable();
            $table->foreignId('prediction_id')->nullable()->constrained('maintenance_predictions', 'prediction_id')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
        });

        Schema::create('fuel_records', function (Blueprint $table) {
            $table->id('fuel_record_id');
            $table->string('code', 40)->unique();
            $table->foreignId('vehicle_id')->constrained('vehicles', 'vehicle_id')->cascadeOnDelete();
            $table->string('fuel_type', 10);
            $table->string('notes', 120)->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->timestampTz('recorded_at')->useCurrent();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id('audit_id');
            $table->string('code', 40)->unique();
            $table->string('action', 40);
            $table->foreignId('user_id')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->string('actor_label', 10)->nullable();
            $table->string('detail', 180)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestampTz('created_at')->useCurrent()->index();
        });
    }
};
