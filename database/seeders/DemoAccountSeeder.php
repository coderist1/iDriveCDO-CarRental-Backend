<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoAccountSeeder extends Seeder
{
    /**
     * Demo logins shared with the frontend's built-in accounts (frontend/shared/js/domain.js).
     * Safe to re-run: accounts are matched by email and their passwords reset.
     */
    public const ACCOUNTS = [
        ['usr_admin', 'admin', 'admin@idrivecdo.ph', 'Drive@Admin1', 'Mara', 'Villanueva', '09171234567',
            'Limketkai Drive, CDO', 'Administration', 'A01-11-100001', '2028-12-31'],
        ['usr_staff', 'staff', 'staff@idrivecdo.ph', 'Drive@Staff1', 'Ken', 'Sable', '09181234567',
            'Divisoria, CDO', 'Rental-Incharge', 'A01-11-100001', '2028-12-31'],
        ['usr_driver', 'driver', 'driver@idrivecdo.ph', 'Drive@Driver1', 'Rico', 'Manalo', '09170001111',
            'Carmen, Cagayan de Oro', 'Chauffeur', 'N02-98-112233', '2028-06-30'],
        ['usr_customer', 'customer', 'guest@idrivecdo.ph', 'Drive@Guest1', 'Paolo', 'Reyes', '09191234567',
            'Uptown Cagayan de Oro', null, 'N04-12-345678', '2028-12-31'],
    ];

    public function run(): void
    {
        DB::transaction(function () {
            foreach (self::ACCOUNTS as [$code, $role, $email, $password, $first, $last, $phone, $address, $department, $license, $expiry]) {
                $user = User::updateOrCreate(['email' => $email], [
                    'code' => $code,
                    'role' => $role,
                    'password_hash' => $password,
                    'first_name' => $first,
                    'last_name' => $last,
                    'phone' => $phone,
                    'address' => $address,
                    'department' => $department,
                    'license_no' => $license,
                    'license_expiry' => $expiry,
                    'status' => 'active',
                    'age_confirmed_at' => $role === 'customer' ? now() : null,
                    'terms_accepted_at' => $role === 'customer' ? now() : null,
                ]);

                $user->forceFill(['email_verified_at' => $user->email_verified_at ?? now()])->save();

                if ($role === 'driver') {
                    $driver = Driver::withTrashed()->updateOrCreate(['user_id' => $user->user_id], [
                        'full_name' => $user->name,
                        'driver_license' => $license,
                        'type_driver_license' => 'Professional',
                        'license_expiry' => $expiry,
                        'phone' => $phone,
                        'status' => 'active',
                        'duty_status' => 'regular',
                    ]);

                    if ($driver->trashed()) {
                        $driver->restore();
                    }
                }
            }
        });
    }
}
