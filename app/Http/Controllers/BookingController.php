<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Response;
use Purifier;
use Auth;
use JWTAuth;

use App\Booking;

class BookingController extends Controller {
    public function store(Request $request) {
        $rules = [
            'name' => 'required|string',
            'email' => 'required|string',
            'spaceID' => 'required|string',
            'type' => 'required|string',
            'day' => 'required|string',
            'start' => 'required|string',
            'end' => 'required|string',
        ];

        // Validate input against rules
        $validator = Validator::make(Purifier::clean($request->all()), $rules);

        if ($validator->fails()) {
            return Response::json(['error' => 'error']);
        }

        $name = $request->input('name');
        $email = $request->input('email');
        $spaceID = $request->input('spaceID');
        $type = $request->input('type');
        $day = $request->input('day');
        $start = $request->input('start');
        $end = $request->input('end');

        $booking = new App\Booking;
        $booking->name = $name;
        $booking->email = $email;
        $booking->spaceID = $spaceID;
        $booking->type = $type;
        $booking->day = $day;
        $booking->start = $start;
        $booking->end = $end;
        $booking->token = str_random(128);
        $success = $booking->save();

        if (!$success) {
            // handle error
        }    

        // send email to admin
    }

    public function approve($token) {
        $booking = Booking::where('token', $token)->first();
        $booking->status = 'approved'; 
    }

    public function deny($token) {
        $booking = Booking::where('token', $token)->first();
        $booking->status = 'denied'; 
    }

}
