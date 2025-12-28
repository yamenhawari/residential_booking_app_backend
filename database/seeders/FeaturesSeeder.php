<?php

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Seeder;

class FeaturesSeeder extends Seeder
{
    public function run(): void
    {
        $features = [
            'Air Conditioning',
            'Central Heating',
            'Balcony',
            'Furnished Kitchen',
            'Wooden Flooring',
            'Security Door',
            'CCTV Cameras',
            'Doorman',
            'Smoke Detector',
            'High-Speed Internet',
            'Wi-Fi',
            'Backup Generator',
            'Parking Space',
            'Elevator',
            'Cleaning Service',
            'Near Public Transport',
            'Near Schools',
            'Near Shopping Centers',
            'Sea View',
            'City View',
            'Jacuzzi',
            'Private Pool',
            'Gym',
            'Private Garden',
            'PC',
        ];

        foreach ($features as $feature) {
            Feature::create(['name' => $feature]);
        }
    }
}
