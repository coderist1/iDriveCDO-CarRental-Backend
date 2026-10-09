<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TelemetryReadingRequest extends FormRequest
{
    /**
     * Largest absolute value each metric's numeric(p,s) column can hold.
     */
    private const LIMITS = [
        'odometer_reading' => 999999999.9, 'engine_temp_c' => 9999.99, 'engine_rpm' => 999999.9,
        'oil_pressure_psi' => 9999.99, 'coolant_temp_c' => 9999.99, 'fuel_level_percent' => 100,
        'fuel_consumption_lph' => 9999.99, 'engine_load_percent' => 100, 'throttle_pos_percent' => 100,
        'air_flow_rate_gps' => 99999.99, 'exhaust_gas_temp_c' => 9999.99, 'vibration_level' => 999.99,
        'engine_hours' => 99999999.9, 'brake_fluid_level_psi' => 99999.99, 'brake_pad_wear_mm' => 999.99,
        'brake_temp_c' => 9999.99, 'brake_pedal_pos_percent' => 100, 'wheel_speed_fl_kph' => 9999.99,
        'wheel_speed_fr_kph' => 9999.99, 'wheel_speed_rl_kph' => 9999.99, 'wheel_speed_rr_kph' => 9999.99,
        'battery_voltage_v' => 999.99, 'battery_current_a' => 9999.99, 'battery_temp_c' => 9999.99,
        'alternator_output_v' => 999.99, 'battery_charge_percent' => 100, 'battery_health_percent' => 100,
        'vehicle_speed_kph' => 9999.99, 'ambient_temp_c' => 999.99, 'humidity_percent' => 100,
    ];

    /**
     * Metrics that ck_telemetry_non_negative / ck_telemetry_percents require to be >= 0.
     */
    private const NON_NEGATIVE = [
        'odometer_reading', 'engine_rpm', 'engine_hours', 'brake_pad_wear_mm', 'vehicle_speed_kph',
        'oil_pressure_psi', 'fuel_consumption_lph', 'fuel_level_percent', 'engine_load_percent',
        'throttle_pos_percent', 'brake_pedal_pos_percent', 'battery_charge_percent',
        'battery_health_percent', 'humidity_percent',
    ];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'vehicle_id' => ['required', Rule::exists('vehicles', 'vehicle_id')],
            'brand' => ['sometimes', 'string', 'max:30'],
            'reading_time' => ['required', 'date'],
            'abs_fault_indicator' => ['sometimes', 'integer', Rule::in([0, 1])],
            'extra_attributes' => ['sometimes', 'nullable', 'array'],
            'recorded_by' => ['sometimes', 'nullable', Rule::exists('users', 'user_id')],
        ];

        foreach (self::LIMITS as $metric => $max) {
            $min = in_array($metric, self::NON_NEGATIVE, true) ? 0 : -$max;
            $rules[$metric] = ['required', 'numeric', "between:{$min},{$max}"];
        }

        return $rules;
    }
}
