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
        'plate_number',
        'mileage',
        'brand',
        'model',
        'type',
        'capacity',
        'year_model',
        'year_purchased',
    ];

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
