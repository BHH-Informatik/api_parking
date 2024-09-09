<?php

namespace Database\Seeders;

use App\Models\ParkingLot;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Database\Factories\ParkingLotFactory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::factory()->create([
            'first_name' => 'AdminFirstname',
            'last_name' => 'AdminLastname',
            'email' => 'Admin@admin.com'
        ]);

        $user->assignRole('admin');

        User::factory(20)->create();



    }
}
