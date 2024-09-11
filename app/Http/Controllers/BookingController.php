<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ParkingLot;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{

    public function getParkingLots($date) {
        try {
            $validDate = Carbon::createFromFormat('Y-m-d', $date)->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid date format. Expected format: YYYY-MM-DD', 'error' => $e->getMessage()], 400);
        }

        $parkingLots = ParkingLot::all();
        $bookedLots = Booking::where('booking_date', $validDate)->get();

        $user = Auth::user();
        $userId = $user ? $user->id : null;

        $parkingLotData = $parkingLots->map(function ($lot) use ($bookedLots, $userId) {
            $extras = new \stdClass();
            $status = 'FREE';

            $booking = $bookedLots->firstWhere('parking_lot_id', $lot->id);

            if ($booking) {
                if ($booking->booking_start_time) {
                    $status = 'TIMERANGE_BLOCKED';
                    $extras = (object) [
                        'start_time' => $booking->booking_start_time,
                        'end_time' => $booking->booking_end_time,
                        'blocked_by_user' => ($userId == $booking->user_id)
                    ];
                } else {
                    $status = 'FULL_DAY_BLOCKED';
                    $extras = (object) [
                        'blocked_by_user' => ($userId == $booking->user_id)
                    ];
                }
            }

            return [
                'id' => $lot->id,
                'name' => $lot->lot_name,
                'status' => $status,
                'extras' => $extras
            ];
        });

        return response()->json(['message' => 'Getting parking lot information was successful', 'date' => $validDate, 'parking_lots' => $parkingLotData], 200);
    }

}
