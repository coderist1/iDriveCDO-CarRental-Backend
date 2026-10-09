<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenancePrediction extends Model
{
    public const TARGETS = ['failure_imminent', 'engine_failure_imminent', 'brake_issue_imminent', 'battery_issue_imminent'];

    protected $primaryKey = 'prediction_id';

    public $timestamps = false;

    protected $fillable = [
        'vehicle_id',
        'telemetry_id',
        'target',
        'prediction',
        'needs_maintenance',
        'probability',
        'model_name',
        'predicted_by',
        'predicted_at',
    ];

    protected function casts(): array
    {
        return [
            'prediction' => 'integer',
            'needs_maintenance' => 'boolean',
            'probability' => 'decimal:6',
            'predicted_at' => 'datetime',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id')->withTrashed();
    }

    public function telemetryReading(): BelongsTo
    {
        return $this->belongsTo(TelemetryReading::class, 'telemetry_id');
    }

    public function predictor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'predicted_by');
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class, 'prediction_id');
    }
}
