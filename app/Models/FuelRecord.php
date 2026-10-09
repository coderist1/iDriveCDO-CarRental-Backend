<?php

namespace App\Models;

use App\Models\Concerns\HasCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelRecord extends Model
{
    use HasCode, HasFactory;

    public const CODE_PREFIX = 'fuel';

    public const FUEL_TYPES = ['Gasoline', 'Diesel'];

    protected $primaryKey = 'fuel_record_id';

    public $timestamps = false;

    protected $fillable = [
        'code',
        'vehicle_id',
        'fuel_type',
        'notes',
        'recorded_by',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
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
}
