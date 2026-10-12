<?php

namespace App\Http\Requests\Concerns;

use App\Support\Phone;

trait NormalizesAccountPhone
{
    protected function prepareForValidation(): void
    {
        if (! $this->filled('phone')) {
            return;
        }

        $e164 = Phone::toE164((string) $this->input('phone'));
        if ($e164) {
            $this->merge(['phone' => $e164]);
        }
    }
}
