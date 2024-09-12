<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ParkingLot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;


use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;

// @group Booking
// API endpoints for booking
class BookingController extends Controller
{

    /**
     * @group Booking
     * Get parking lot booking information
     *
     * @param date string required The date for which to retrieve parking lot information. Expected format: YYYY-MM-DD
     *
     * @return \Illuminate\Http\JsonResponse
     *
     *
     * @response 200 scenario="Success" {
     *   "message": "Getting parking lot information was successful",
     *   "date": "2023-10-01",
     *   "parking_lots": [
     *     {
     *       "id": 1,
     *       "name": "P1",
     *       "status": "FREE",
     *       "extras": {}
     *     },
     *     {
     *       "id": 2,
     *       "name": "P2",
     *       "status": "TIMERANGE_BLOCKED",
     *       "extras": {
     *         "start_time": "10:00",
     *         "end_time": "12:00",
     *         "blocked_by_user": true
     *       }
     * {
     *        "id": 3,
     *        "name": "P3",
     *        "status": "FULL_DAY_BLOCKED",
     *        "extras": {
     *          "blocked_by_user": false
     *        }
     *     }
     *   ]
     * }
     *
     * @response 400 scenario="Invalid Date Format" {
     *   "message": "Invalid date format. Expected format: YYYY-MM-DD",
     *   "error": "The error message here"
     * }
     */
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


    /**
     * @group Booking
     * Get iCal Calendar
     *
     * @description Zuerst muss ein Kalendertoken über /auth/calendar generiert werden.
     *
     * @urlParam token required The calendar token of the user. Example: 9hC4K....gMzp8nQCrgw
     *
     * @response 200 scenario="Success" ICal-File
     */
    public function getICAL($token) {

        $user = User::where('calendar_token', $token)->first();
        if(!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $calendar = Calendar::create($user->first_name . " " . $user->last_name . " Parkplatz");

        $bookings = Booking::where('user_id', $user->id)->get();

        // return response()->json($bookings);
        foreach($bookings as $booking) {
            $parkingLot = ParkingLot::find($booking->parking_lot_id);

            if($booking->booking_start_time) {
                $event = Event::create()
                    ->name('Parkplatz ' . $parkingLot->lot_name)
                    ->description('Parkplatz ' . $parkingLot->lot_name . ' gebucht')
                    ->startsAt(Carbon::parse($booking->booking_date)->setTimeFromTimeString($booking->booking_start_time))
                    ->endsAt(Carbon::parse($booking->booking_date)->setTimeFromTimeString($booking->booking_end_time));
            }else{
                $event = Event::create()
                    ->name('Parkplatz ' . $parkingLot->lot_name)
                    ->description('Parkplatz ' . $parkingLot->lot_name . ' gebucht')
                    ->startsAt(Carbon::parse($booking->booking_date))
                    ->endsAt(Carbon::parse($booking->booking_date)->addDay());
            }

            $calendar->event($event);
        }

        return response($calendar->get(), 200, [
            'Content-Type' => 'text/calendar',
            'Content-Disposition' => 'attachment; filename="parkplatz.ics"'
        ]);
    }

}
