<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Page size from ?per_page=, clamped to 1..200 (default 15).
     */
    protected function perPage(Request $request): int
    {
        return min(max((int) $request->query('per_page', 15), 1), 200);
    }
}
