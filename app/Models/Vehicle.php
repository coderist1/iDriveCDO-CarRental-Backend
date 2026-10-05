<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $primaryKey = 'vehicle_id';

    protected $fillable = [
        'code',
        'name',
        'plate_number',
        'mileage',
        'brand',
        'model',
        'type',
        'transmission',
        'fuel',
        'capacity',
        'luggage',
        'daily_rate',
        'status',
        'image',
        'description',
        'features',
        'year_model',
        'year_purchased',
    ];

    protected function casts(): array
    {
        return [
            'daily_rate' => 'decimal:2',
            'features' => 'array',
        ];
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'vehicle_id', 'vehicle_id');
    }

    public function regDetails(): HasMany
    {
        return $this->hasMany(VehicleRegDetail::class, 'vehicle_id', 'vehicle_id');
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(VehicleMaintenance::class, 'vehicle_id', 'vehicle_id');
    }

    public function fuelRecords(): HasMany
    {
        return $this->hasMany(FuelRecord::class, 'vehicle_id', 'vehicle_id');
    }
}
