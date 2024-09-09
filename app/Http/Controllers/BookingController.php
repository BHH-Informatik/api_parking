<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ParkingLot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{

    public function getParkingLots($date) {

        $dateTime = \DateTime::createFromFormat('Y-m-d', $date);

        if (!$dateTime) {
            return response()->json(['message' => 'Invalid date format. Expected format: YYYY-MM-DD'], 400);
        }

        $parkingLots = ParkingLot::all();

        $bookedLots = Booking::where('booking_date', $date)->pluck('parking_lot_id')->toArray();

        $parkingLotData = $parkingLots->map(function ($lot) use ($bookedLots) {
            return [
                'id' => $lot->id,
                'name' => $lot->lot_name,
                'status' => in_array($lot->id, $bookedLots) ? 'BLOCKED' : 'FREE',
                'extras' => new \stdClass(),
            ];
        });

        return response()->json(['message' => 'Getting parking lot information was successful', 'date' => $date, 'parking_lots' => $parkingLotData], 200);
    }
}
