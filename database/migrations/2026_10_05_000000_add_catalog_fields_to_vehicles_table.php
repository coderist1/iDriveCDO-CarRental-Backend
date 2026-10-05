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
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('code', 40)->nullable()->unique()->after('vehicle_id');
            $table->string('name', 100)->nullable()->after('code');
            $table->string('transmission', 20)->nullable()->after('type');
            $table->string('fuel', 20)->nullable()->after('transmission');
            $table->unsignedTinyInteger('luggage')->default(2)->after('capacity');
            $table->string('status', 20)->default('available')->after('daily_rate');
            $table->string('image', 500)->nullable()->after('status');
            $table->text('description')->nullable()->after('image');
            $table->json('features')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn([
                'code',
                'name',
                'transmission',
                'fuel',
                'luggage',
                'status',
                'image',
                'description',
                'features',
            ]);
        });
    }
};
