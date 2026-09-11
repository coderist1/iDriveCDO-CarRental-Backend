<?php

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CustomerInfoController;
use App\Http\Controllers\Api\DriverDetailController;
use App\Http\Controllers\Api\FuelRecordController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\StaffInfoController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\VehicleMaintenanceController;
use App\Http\Controllers\Api\VehicleRegDetailController;
use Illuminate\Support\Facades\Route;

Route::apiResource('vehicles', VehicleController::class)
    ->parameters(['vehicles' => 'vehicle']);

Route::apiResource('driver-details', DriverDetailController::class)
    ->parameters(['driver-details' => 'driver_detail']);

Route::apiResource('bookings', BookingController::class)
    ->parameters(['bookings' => 'booking']);

Route::apiResource('payments', PaymentController::class)
    ->parameters(['payments' => 'payment']);

Route::apiResource('fuel-records', FuelRecordController::class)
    ->parameters(['fuel-records' => 'fuel_record']);

Route::apiResource('vehicle-maintenances', VehicleMaintenanceController::class)
    ->parameters(['vehicle-maintenances' => 'vehicle_maintenance']);

Route::apiResource('vehicle-reg-details', VehicleRegDetailController::class)
    ->parameters(['vehicle-reg-details' => 'vehicle_reg_detail']);

Route::apiResource('staff-info', StaffInfoController::class)
    ->parameters(['staff-info' => 'staff_info']);

Route::apiResource('customer-info', CustomerInfoController::class)
    ->parameters(['customer-info' => 'customer_info']);
