<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleFeature extends Model
{
    protected $primaryKey = 'vehicle_feature_id';

    public $timestamps = false;

    protected $fillable = [
        'vehicle_id',
        'feature',
        'sort_order',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }
}
