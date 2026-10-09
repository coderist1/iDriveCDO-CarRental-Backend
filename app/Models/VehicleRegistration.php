<?php

namespace App\Models;

use App\Models\Concerns\HasCode;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleRegistration extends Model
{
    use HasCode, HasFactory;

    public const CODE_PREFIX = 'reg';

    protected $primaryKey = 'registration_id';

    protected $fillable = [
        'code',
        'vehicle_id',
        'plate_number',
        'renewal_scheduled_day',
        'next_reg_renewal',
    ];

    protected function casts(): array
    {
        return [
            'renewal_scheduled_day' => 'integer',
            'next_reg_renewal' => 'date:Y-m-d',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id')->withTrashed();
    }
}
