<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TelemetryReading extends Model
{
    /**
     * Sensor columns, all numeric and required.
     */
    public const METRICS = [
        'odometer_reading', 'engine_temp_c', 'engine_rpm', 'oil_pressure_psi', 'coolant_temp_c',
        'fuel_level_percent', 'fuel_consumption_lph', 'engine_load_percent', 'throttle_pos_percent',
        'air_flow_rate_gps', 'exhaust_gas_temp_c', 'vibration_level', 'engine_hours',
        'brake_fluid_level_psi', 'brake_pad_wear_mm', 'brake_temp_c', 'brake_pedal_pos_percent',
        'wheel_speed_fl_kph', 'wheel_speed_fr_kph', 'wheel_speed_rl_kph', 'wheel_speed_rr_kph',
        'battery_voltage_v', 'battery_current_a', 'battery_temp_c', 'alternator_output_v',
        'battery_charge_percent', 'battery_health_percent', 'vehicle_speed_kph', 'ambient_temp_c',
        'humidity_percent',
    ];

    public const UPDATED_AT = null;

    protected $primaryKey = 'telemetry_id';

    /**
     * reading_hour and reading_day_of_week are generated columns and are never written.
     */
    protected $fillable = [
        'vehicle_id',
        'brand',
        'reading_time',
        ...self::METRICS,
        'abs_fault_indicator',
        'extra_attributes',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'reading_time' => 'datetime',
            'reading_hour' => 'integer',
            'reading_day_of_week' => 'integer',
            'abs_fault_indicator' => 'integer',
            'extra_attributes' => 'array',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id')->withTrashed();
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function predictions(): HasMany
    {
        return $this->hasMany(MaintenancePrediction::class, 'telemetry_id');
    }
}
