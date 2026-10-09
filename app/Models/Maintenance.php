<?php

namespace App\Models;

use App\Models\Concerns\HasCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Maintenance extends Model
{
    use HasCode, HasFactory;

    public const CODE_PREFIX = 'mnt';

    protected $primaryKey = 'maintenance_id';

    protected $fillable = [
        'code',
        'vehicle_id',
        'maintenance_type',
        'scheduled_date',
        'performed_at',
        'finished',
        'notes',
        'prediction_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date:Y-m-d',
            'performed_at' => 'date:Y-m-d',
            'finished' => 'boolean',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id')->withTrashed();
    }

    public function prediction(): BelongsTo
    {
        return $this->belongsTo(MaintenancePrediction::class, 'prediction_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
