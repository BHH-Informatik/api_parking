<?php

namespace Database\Seeders;

use App\Models\ParkingLot;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ParkingLotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            ParkingLot::factory()->create([
                'lot_name' => 'P' . $i,
            ]);
        }
    }
}
