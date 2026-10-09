<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Read-only access to the reporting views defined in database/idrive_schema.sql.
 */
class ReportController extends Controller
{
    private const VIEWS = [
        'dashboard-summary' => 'v_dashboard_summary',
        'booking-status-counts' => 'v_booking_status_counts',
        'revenue-by-vehicle-type' => 'v_revenue_by_vehicle_type',
        'fleet-repair-stats' => 'v_fleet_repair_stats',
        'registration-renewals-due' => 'v_registration_renewals_due',
        'vehicle-latest-telemetry' => 'v_vehicle_latest_telemetry',
        'vehicle-latest-prediction' => 'v_vehicle_latest_prediction',
        'vehicle-rating-summary' => 'v_vehicle_rating_summary',
    ];

    /**
     * Single-row views are returned as an object instead of a list.
     */
    private const SINGLE_ROW = ['dashboard-summary', 'fleet-repair-stats'];

    public function index(): JsonResponse
    {
        return response()->json(array_keys(self::VIEWS));
    }

    public function show(string $report): JsonResponse
    {
        abort_unless(isset(self::VIEWS[$report]), 404, 'Unknown report.');

        $query = DB::table(self::VIEWS[$report]);

        return response()->json(in_array($report, self::SINGLE_ROW, true) ? $query->first() : $query->get());
    }
}
