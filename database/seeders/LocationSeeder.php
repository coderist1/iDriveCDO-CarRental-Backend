<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Seed the pickup and drop-off points offered by the frontend.
     */
    public function run(): void
    {
        $locations = [
            'iDrive CDO Main Office',
            'Laguindingan Airport',
            'SM City CDO Uptown',
            'Centrio Mall',
            'Limketkai Center',
            'Agora Bus Terminal',
            'Macabalan Port',
        ];

        foreach ($locations as $order => $name) {
            Location::updateOrCreate(['name' => $name], ['is_active' => true, 'sort_order' => $order]);
        }
    }
}
