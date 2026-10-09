<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Fills the unique `code` column (e.g. "veh_k3j9x0q2m1ab") when a record is created without one.
 * Models using this trait define a CODE_PREFIX constant.
 */
trait HasCode
{
    public static function bootHasCode(): void
    {
        static::creating(function (self $model) {
            if (blank($model->code)) {
                $model->code = static::CODE_PREFIX.'_'.Str::lower(Str::random(12));
            }
        });
    }
}
