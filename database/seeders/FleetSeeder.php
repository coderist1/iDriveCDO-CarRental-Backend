<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class FleetSeeder extends Seeder
{
    /**
     * Seed the rental fleet shown by the iDrive CDO frontend.
     */
    public function run(): void
    {
        $fleet = [
            ['veh_vios', 'Toyota Vios 2024', 'Toyota', 'Vios', 2024, 'Sedan', 'Automatic', 'Gasoline', 5, 2, 1800, 'CDO-1001',
                'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?auto=format&fit=crop&w=1400&q=80',
                'City-friendly sedan for downtown CDO, mall runs, and airport transfers.',
                ['Bluetooth', 'Backup camera', 'USB charging', 'Fuel efficient']],
            ['veh_city', 'Honda City 2023', 'Honda', 'City', 2023, 'Sedan', 'Automatic', 'Gasoline', 5, 2, 1900, 'CDO-1002',
                'https://images.unsplash.com/photo-1590362891991-f776e747a588?auto=format&fit=crop&w=1400&q=80',
                'Quiet cabin and strong air-conditioning for long CDO heat.',
                ['Cruise control', 'Apple CarPlay', 'Keyless entry']],
            ['veh_mirage', 'Mitsubishi Mirage G4', 'Mitsubishi', 'Mirage G4', 2023, 'Sedan', 'Automatic', 'Gasoline', 5, 1, 1500, 'CDO-1003',
                'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&w=1400&q=80',
                'Light on fuel. Ideal for solo travelers and couples.',
                ['Compact park', 'Touchscreen', 'Eco mode']],
            ['veh_fortuner', 'Toyota Fortuner', 'Toyota', 'Fortuner', 2024, 'SUV', 'Automatic', 'Diesel', 7, 4, 4500, 'CDO-2001',
                'https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?auto=format&fit=crop&w=1400&q=80',
                'Family SUV for Bukidnon roads, Camiguin trips, and group tours.',
                ['4x2', 'Third row', 'Hill assist', 'Rear AC']],
            ['veh_montero', 'Mitsubishi Montero Sport', 'Mitsubishi', 'Montero Sport', 2023, 'SUV', 'Automatic', 'Diesel', 7, 4, 4300, 'CDO-2002',
                'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1400&q=80',
                'High clearance for CDO rain days and mountain weekends.',
                ['Leather seats', 'Paddle shift', 'Camera 360']],
            ['veh_crv', 'Honda CR-V', 'Honda', 'CR-V', 2024, 'SUV', 'Automatic', 'Gasoline', 5, 3, 3800, 'CDO-2003',
                'https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?auto=format&fit=crop&w=1400&q=80',
                'Comfortable crossover for city hotels and uptown stays.',
                ['Honda Sensing', 'Sunroof', 'Power tailgate']],
            ['veh_everest', 'Ford Everest', 'Ford', 'Everest', 2023, 'SUV', 'Automatic', 'Diesel', 7, 4, 4200, 'CDO-2004',
                'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=1400&q=80',
                'Strong towing and highway presence for northbound trips.',
                ['Terrain modes', 'SYNC infotainment', 'LED lights']],
            ['veh_hiace', 'Toyota Hiace Commuter', 'Toyota', 'Hiace', 2023, 'Van', 'Manual', 'Diesel', 15, 8, 5000, 'CDO-3001',
                'https://images.unsplash.com/photo-1527786356703-4b100091cd2c?auto=format&fit=crop&w=1400&q=80',
                'Group van for company outings, church trips, and airport batches.',
                ['High roof', 'Dual AC', 'Wide sliding doors']],
            ['veh_alphard', 'Toyota Alphard', 'Toyota', 'Alphard', 2022, 'Van', 'Automatic', 'Gasoline', 7, 4, 8500, 'CDO-3002',
                'https://images.unsplash.com/photo-1619767886558-efdc259cde1a?auto=format&fit=crop&w=1400&q=80',
                'Executive van with captain seats for VIP airport arrivals.',
                ['Captain seats', 'Power sliding doors', 'Premium audio']],
            ['veh_hilux', 'Toyota Hilux Conquest', 'Toyota', 'Hilux', 2024, 'Pickup', 'Automatic', 'Diesel', 5, 5, 3600, 'CDO-4001',
                'https://images.unsplash.com/photo-1559416523-140ddc3d238c?auto=format&fit=crop&w=1400&q=80',
                'Workhorse pickup for cargo, site visits, and rough farm roads.',
                ['4x4', 'Bed liner', 'Tow hook']],
            ['veh_ranger', 'Ford Ranger Wildtrak', 'Ford', 'Ranger', 2024, 'Pickup', 'Automatic', 'Diesel', 5, 5, 3900, 'CDO-4002',
                'https://images.unsplash.com/photo-1609521263047-f8f205293f24?auto=format&fit=crop&w=1400&q=80',
                'Lifestyle pickup for beach gear and weekend camping.',
                ['Sports bar', 'Leather-trimmed', 'Off-road tires']],
            ['veh_landcruiser', 'Toyota Land Cruiser Prado', 'Toyota', 'Prado', 2022, 'SUV', 'Automatic', 'Diesel', 7, 4, 7800, 'CDO-2005',
                'https://images.unsplash.com/photo-1544636331-e26879cd4d9b?auto=format&fit=crop&w=1400&q=80',
                'Flagship SUV for long north Mindanao itineraries.',
                ['Crawl control', 'Cool box', 'Multi-terrain']],
        ];

        foreach ($fleet as $i => [$code, $name, $brand, $model, $year, $type, $transmission, $fuel, $seats, $luggage, $rate, $plate, $image, $description, $features]) {
            $vehicle = Vehicle::withTrashed()->updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'plate_number' => $plate,
                    'mileage' => 12000 + $i * 1500,
                    'brand' => $brand,
                    'model' => $model,
                    'type' => $type,
                    'transmission' => $transmission,
                    'fuel' => $fuel,
                    'capacity' => $seats,
                    'luggage' => $luggage,
                    'daily_rate' => $rate,
                    'status' => 'available',
                    'image' => $image,
                    'description' => $description,
                    'year_model' => $year,
                    'year_purchased' => $year,
                ]
            );

            $vehicle->syncFeatures($features);
        }
    }
}
