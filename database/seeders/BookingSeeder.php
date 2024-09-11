<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Booking;
use App\Models\ParkingLot;
use App\Models\User;

use Carbon\Carbon;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void {

        // Clear Booking table
        Booking::truncate();


        $adminUser = User::where('email', 'friedrich@enten.dev')->first();

        // 10 times
        for($i = 0; $i < 10; $i++) {

            // random day between today and 30 days from now
            $randomDay = Carbon::today()->addDays(rand(0, 30));


            $randomParkinglot = ParkingLot::inRandomOrder()->first();
            // 50% chance
            if(rand(0, 1) == 1) {
                Booking::create([
                    'user_id' => $adminUser->id,
                    'parking_lot_id' => $randomParkinglot->id,
                    'booking_date' => $randomDay,
                ]);
            }else{

                $randomDateTime = Carbon::parse($randomDay)->addHours(rand(0, 23));

                Booking::create([
                    'user_id' => $adminUser->id,
                    'parking_lot_id' => $randomParkinglot->id,
                    'booking_date' => $randomDay,
                    'booking_start_time' => $randomDateTime,
                    'booking_end_time' => Carbon::parse($randomDateTime)->addHours(rand(1, 23)),
                ]);
            }
        }


    }
}
