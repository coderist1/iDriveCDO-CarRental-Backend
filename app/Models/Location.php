<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $primaryKey = 'location_id';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function pickups(): HasMany
    {
        return $this->hasMany(Booking::class, 'pickup_location_id');
    }

    public function dropoffs(): HasMany
    {
        return $this->hasMany(Booking::class, 'dropoff_location_id');
    }
}
