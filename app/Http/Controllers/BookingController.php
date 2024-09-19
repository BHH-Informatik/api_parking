<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ParkingLot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


use Illuminate\Support\Facades\Validator;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;

// @group Booking
// API endpoints for booking
class BookingController extends Controller
{
    public function __construct()
    {
        Validator::extend('nullable_if_both_null', function ($attribute, $value, $parameters, $validator) {
            $startTime = $validator->getData()['start_time'] ?? null;
            $endTime = $validator->getData()['end_time'] ?? null;

            if (is_null($startTime) && is_null($endTime)) {
                return true;
            }

            return !is_null($value);
        });
    }

    /**
     * @group Booking
     * Get parking lot booking information
     *
     * @param date string required The date for which to retrieve parking lot information. Expected format: YYYY-MM-DD
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @unauthenticated
     *
     * @response 200 scenario="Success" {
     *   "message": "Getting parking lot information was successful",
     *   "date": "2023-09-27",
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
     *         "booking_id": 24
     *         "start_time": "10:00",
     *         "end_time": "12:00",
     *         "blocked_by_user": true
     *       }
     * {
     *        "id": 3,
     *        "name": "P3",
     *        "status": "FULL_DAY_BLOCKED",
     *        "extras": {
     *          "booking_id": 23
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
                        'booking_id' => $booking->id,
                        'start_time' => $booking->booking_start_time,
                        'end_time' => $booking->booking_end_time,
                        'blocked_by_user' => ($userId == $booking->user_id)
                    ];
                } else {
                    $status = 'FULL_DAY_BLOCKED';
                    $extras = (object) [
                        'booking_id' => $booking->id,
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
     * Get all bookings information
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @authenticated
     *
     * @response 200 scenario="Success" {
     *   "message": "Getting booking information was successful",
     *   "bookings": [
     *     {
     *       "id": 1,
     *       "user_id": 2,
     *       "parking_lot_id": 3,
     *       "booking_date": "2023-09-27",
     *       "booking_start_time": "10:00",
     *       "booking_end_time": "12:00"
     *     },
     *     {
     *       "id": 2,
     *       "user_id": 3,
     *       "parking_lot_id": 1,
     *       "booking_date": "2023-09-28",
     *       "booking_start_time": "14:00",
     *       "booking_end_time": "16:00"
     *     }
     *   ]
     * }
     *
     * @response 500 scenario="Internal Server Error" {
     *   "message": "Getting booking information failed",
     *   "error": "The error message here"
     * }
     */
    public function getBookings() {
        $bookings = Booking::all();
        return response()->json(['message' => 'Getting booking information was successful', 'bookings' => $bookings], 200);
    }

    /**
     * @group Booking
     * Get own bookings information
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @authenticated
     *
     * @response 200 scenario="Success" {
     *   "message": "Getting booking information was successful",
     *   "bookings": [
     *     {
     *       "id": 1,
     *       "user_id": 2,
     *       "parking_lot_id": 3,
     *       "booking_date": "2023-09-27",
     *       "booking_start_time": "10:00",
     *       "booking_end_time": "12:00"
     *     },
     *     {
     *       "id": 2,
     *       "user_id": 2,
     *       "parking_lot_id": 1,
     *       "booking_date": "2023-09-28",
     *       "booking_start_time": null,
     *       "booking_end_time": null
     *     }
     *   ]
     * }
     *
     * @response 404 scenario="No Bookings Found" {
     *   "message": "No bookings found for this user"
     * }
     *
     * @response 500 scenario="Internal Server Error" {
     *   "message": "Getting booking information failed",
     *   "error": "The error message here"
     * }
     */
    public function getOwnBookings() {
        $userId = Auth::user()->id;
        $bookings = Booking::where('user_id', $userId)->get();
        return response()->json(['message' => 'Getting booking information was successful', 'bookings' => $bookings], 200);
    }

    /**
     * @group Booking
     * Book a parking lot
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @authenticated
     *
     * @bodyParam parking_lot_id int required The ID of the parking lot to be booked. Example: 1
     * @bodyParam booking_date string required The date for the booking. Expected format: YYYY-MM-DD. Example: 2023-09-27
     * @bodyParam start_time string optional The start time for the booking.Expected format: HH:MM. Example: 10:00
     * @bodyParam end_time string optional The end time for the booking.Expected format: HH:MM. Example: 12:00
     *
     * @response 201 scenario="Success" {
     *   "message": "Parking lot booked successfully",
     *   "booking": {
     *     "id": 1,
     *     "user_id": 2,
     *     "parking_lot_id": 1,
     *     "booking_date": "2023-09-27",
     *     "booking_start_time": "10:00",
     *     "booking_end_time": "12:00"
     *   }
     * }
     *
     * @response 400 scenario="User Already Has Booking" {
     *   "message": "The user already has a reservation for a parking lot on this day"
     * }
     *
     * @response 400 scenario="Parking Lot Already Booked" {
     *   "message": "The parking lot is already booked for this time"
     * }
     *
     * @response 422 scenario="Validation Error" {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "parking_lot_id": [
     *       "The selected parking lot id is invalid."
     *     ],
     *     "booking_date": [
     *       "The booking date is required.",
     *       "The booking date must be a valid date."
     *     ]
     *   }
     * }
     */
    public function bookParkingLot(Request $request) {

        $request->validate([
            'parking_lot_id' => 'required|exists:parking_lots,id',
            'booking_date' => 'required|date_format:Y-m-d',
            'start_time' => 'nullable_if_both_null|date_format:H:i',
            'end_time' => 'nullable_if_both_null|date_format:H:i|after:start_time'
        ]);

        $userId = Auth::user()->id;

        $existingBookingFromUser = Booking::where('user_id', $userId)->where('booking_date', $request->booking_date)->first();

        if ($existingBookingFromUser !== null) {
            return response()->json(['message' => 'The user already has a reservation for a parking lot on this day'], 400);
        }

        $startTime = $request->start_time;
        $endTime = $request->end_time;

        if ($startTime === null) {
            $isBooked = Booking::where('parking_lot_id', $request->parking_lot_id)->where('booking_date', $request->booking_date)->exists();
        } else {
            $isBooked = Booking::where('booking_date', $request->booking_date)
                ->where('parking_lot_id', $request->parking_lot_id)
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->where(function ($query) use ($startTime, $endTime) {
                        $query->whereBetween('start_time', [$startTime, $endTime])
                            ->orWhereBetween('end_time', [$startTime, $endTime])
                            ->orWhere(function ($query) use ($startTime, $endTime) {
                                $query->where('start_time', '<', $startTime)
                                    ->where('end_time', '>', $endTime);
                            });
                    })->orWhereNull('start_time');
                })
                ->exists();
        }

        if ($isBooked) {
            return response()->json(['message' => 'The parking lot is already booked for this time'], 400);
        }

        $booking = Booking::create([
            'user_id' => $userId,
            'parking_lot_id' => $request->parking_lot_id,
            'booking_date' => $request->booking_date,
            'booking_start_time' => $request->start_time,
            'booking_end_time' => $request->end_time
        ]);

        return response()->json(['message' => 'Parking lot booked successfully', 'booking' => $booking], 201);
    }

    /**
     * @group Booking
     * Book a free parking lot
     *
     * This endpoint allows users to book a parking lot without selecting a parking lot for a specific date and time.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @authenticated
     *
     * @bodyParam booking_date string required The date for the booking. Expected format: YYYY-MM-DD. Example: 2023-09-27
     * @bodyParam start_time string optional The start time for the booking. Expected format: HH:MM. Example: 10:00
     * @bodyParam end_time string optional The end time for the booking. Expected format: HH:MM. Example: 12:00
     *
     * @response 201 scenario="Success" {
     *   "message": "Parking lot booked successfully",
     *   "booking": {
     *     "id": 1,
     *     "user_id": 2,
     *     "parking_lot_id": 1,
     *     "booking_date": "2023-09-27",
     *     "booking_start_time": "10:00",
     *     "booking_end_time": "12:00"
     *   }
     * }
     *
     * @response 400 scenario="User Already Has Booking" {
     *   "message": "The user already has a reservation for a parking lot on this day"
     * }
     *
     * @response 400 scenario="Parking Lot Already Booked" {
     *   "message": "There are no available parking lots for this time."
     * }
     *
     * @response 422 scenario="Validation Error" {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "parking_lot_id": [
     *       "The selected parking lot id is invalid."
     *     ],
     *     "booking_date": [
     *       "The booking date is required.",
     *       "The booking date must be a valid date."
     *     ],
     *     "start_time": [
     *       "The start time must be a valid time."
     *     ],
     *     "end_time": [
     *       "The end time must be a valid time.",
     *       "The end time must be after the start time."
     *     ]
     *   }
     * }
     */
    public function bookFreeParkingLot(Request $request) {
        $request->validate([
            'booking_date' => 'required|date_format:Y-m-d',
            'start_time' => 'nullable_if_both_null|date_format:H:i',
            'end_time' => 'nullable_if_both_null|date_format:H:i|after:start_time'
        ]);

        $startTime = $request->start_time;
        $endTime = $request->end_time;

        $bookedParkingLots = Booking::where('booking_date', $request->booking_date)
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($query) use ($startTime, $endTime) {
                    $query->whereBetween('start_time', [$startTime, $endTime])
                        ->orWhereBetween('end_time', [$startTime, $endTime])
                        ->orWhere(function ($query) use ($startTime, $endTime) {
                            $query->where('start_time', '<', $startTime)
                                ->where('end_time', '>', $endTime);
                        });
                })->orWhereNull('start_time');
            })->pluck('parking_lot_id');

        $availableParkingLot = ParkingLot::whereNotIn('id', $bookedParkingLots)->first();

        if ($availableParkingLot === null) {
            return response()->json(['message' => 'There are no available parking lots for this time.'], 400);
        }

        $userId = Auth::user()->id;

        $existingBookingFromUser = Booking::where('user_id', $userId)->where('booking_date', $request->booking_date)->first();

        if ($existingBookingFromUser !== null) {
            return response()->json(['message' => 'The user already has a reservation for a parking lot on this day'], 400);
        }

        $booking = Booking::create([
            'user_id' => $userId,
            'parking_lot_id' => $availableParkingLot,
            'booking_date' => $request->booking_date,
            'booking_start_time' => $request->start_time,
            'booking_end_time' => $request->end_time
        ]);

        return response()->json(['message' => 'Parking lot booked successfully', 'booking' => $booking], 201);
    }

    /**
     * @group Booking
     * Cancel a booking
     *
     * @param int $id required The ID of the booking to be canceled. Example: 1
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @authenticated
     *
     * @response 200 scenario="Success" {
     *   "message": "Booking successfully deleted"
     * }
     *
     * @response 403 scenario="Unauthorized" {
     *   "message": "You are not authorized to delete this booking"
     * }
     *
     * @response 404 scenario="Booking Not Found" {
     *   "message": "No booking found for the given ID"
     * }
     *
     * @response 400 scenario="Deletion Failed" {
     *   "message": "Deletion of booking failed",
     *   "error": "The error message here"
     * }
     */
    public function cancelBooking($id) {
        try {
            $booking = Booking::findOrFail($id);
            $user = Auth::user();
            if ($user->hasRole('admin') || $user->id == $booking->user_id) {
                $booking->delete();
                return response()->json(['message' => 'Booking successfully deleted'], 200);
            }
            return response()->json(['message' => 'You are not authorized to delete this booking'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Deletion of booking failed', 'error' => $e->getMessage()], 400);
        }
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
