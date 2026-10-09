<?php

namespace App\Models;

use App\Models\Concerns\HasCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasCode, HasFactory, SoftDeletes;

    public const CODE_PREFIX = 'veh';

    public const TYPES = ['Sedan', 'SUV', 'Van', 'Pickup'];

    public const TRANSMISSIONS = ['Automatic', 'Manual'];

    public const FUELS = ['Gasoline', 'Diesel'];

    public const STATUSES = ['available', 'maintenance'];

    protected $primaryKey = 'vehicle_id';

    protected $fillable = [
        'code',
        'name',
        'brand',
        'model',
        'year_model',
        'year_purchased',
        'type',
        'transmission',
        'fuel',
        'capacity',
        'luggage',
        'mileage',
        'daily_rate',
        'plate_number',
        'image',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'year_model' => 'integer',
            'year_purchased' => 'integer',
            'capacity' => 'integer',
            'luggage' => 'integer',
            'mileage' => 'integer',
            'daily_rate' => 'decimal:2',
        ];
    }

    public function features(): HasMany
    {
        return $this->hasMany(VehicleFeature::class, 'vehicle_id')->orderBy('sort_order');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'vehicle_id');
    }

    public function registration(): HasOne
    {
        return $this->hasOne(VehicleRegistration::class, 'vehicle_id');
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class, 'vehicle_id');
    }

    public function fuelRecords(): HasMany
    {
        return $this->hasMany(FuelRecord::class, 'vehicle_id');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class, 'vehicle_id');
    }

    public function telemetryReadings(): HasMany
    {
        return $this->hasMany(TelemetryReading::class, 'vehicle_id');
    }

    public function maintenancePredictions(): HasMany
    {
        return $this->hasMany(MaintenancePrediction::class, 'vehicle_id');
    }

    /**
     * Replace the vehicle's feature list, keeping the given order.
     *
     * @param  list<string>  $features
     */
    public function syncFeatures(array $features): void
    {
        $this->features()->delete();

        foreach (array_values(array_unique($features)) as $order => $feature) {
            $this->features()->create(['feature' => $feature, 'sort_order' => $order]);
        }
    }
}
