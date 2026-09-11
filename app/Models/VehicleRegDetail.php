<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleRegDetail extends Model
{
    use HasFactory;

    protected $table = 'vehicle_reg_details';

    protected $primaryKey = 'vehicle_reg_det_id';

    protected $fillable = [
        'vehicle_id',
        'plate_number',
        'renewal_scheduled_day',
        'next_reg_renewal',
    ];

    protected function casts(): array
    {
        return [
            'renewal_scheduled_day' => 'date',
            'next_reg_renewal' => 'date',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'vehicle_id');
    }
}
