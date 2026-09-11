<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelRecord extends Model
{
    use HasFactory;

    protected $primaryKey = 'fuel_record_id';

    protected $fillable = [
        'vehicle_id',
        'fuel_type',
        'quantity',
        'fuel_cost',
        'fuel_date',
        'mileage',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'fuel_cost' => 'decimal:2',
            'mileage' => 'decimal:2',
            'fuel_date' => 'date',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'vehicle_id');
    }
}
