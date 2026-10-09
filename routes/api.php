<?php

use App\Http\Controllers\Api\AddonController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\DriverController;
use App\Http\Controllers\Api\FuelRecordController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Controllers\Api\MaintenancePredictionController;
use App\Http\Controllers\Api\MessageThreadController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\Api\TelemetryReadingController;
use App\Http\Controllers\Api\ThreadMessageController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\VehicleRegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('sync', SyncController::class);

Route::post('users/sync', [UserController::class, 'sync']);
Route::apiResource('users', UserController::class);

Route::apiResource('vehicles', VehicleController::class);
Route::apiResource('vehicle-registrations', VehicleRegistrationController::class);
Route::apiResource('maintenances', MaintenanceController::class);
Route::apiResource('fuel-records', FuelRecordController::class);
Route::apiResource('telemetry-readings', TelemetryReadingController::class)->except('update');
Route::apiResource('maintenance-predictions', MaintenancePredictionController::class)->except('update');

Route::apiResource('drivers', DriverController::class);
Route::apiResource('locations', LocationController::class);
Route::apiResource('addons', AddonController::class);

Route::apiResource('bookings', BookingController::class);
Route::apiResource('payments', PaymentController::class);
Route::apiResource('ratings', RatingController::class);

Route::apiResource('message-threads', MessageThreadController::class);
Route::get('message-threads/{message_thread}/messages', [ThreadMessageController::class, 'index']);
Route::post('message-threads/{message_thread}/messages', [ThreadMessageController::class, 'store']);

Route::apiResource('audit-logs', AuditLogController::class)->only(['index', 'store', 'show']);

Route::get('reports', [ReportController::class, 'index']);
Route::get('reports/{report}', [ReportController::class, 'show']);
