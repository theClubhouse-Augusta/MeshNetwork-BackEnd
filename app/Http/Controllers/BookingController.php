<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Response;
use Purifier;
use Auth;
use JWTAuth;
use Mail;

use App\Booking;

class BookingController extends Controller {
    public function store(Request $request) {
        $rules = [
            'name' => 'required|string',
            'email' => 'required|string',
            'spaceID' => 'required|string',
            'type' => 'required|string',
            'times' => 'required|string',
        ];

        // Validate input against rules
        $validator = Validator::make(Purifier::clean($request->all()), $rules);

        if ($validator->fails()) {
            return Response::json(['error' => 'Please fill out all fields.']);
        }

        $name = $request->input('name');
        $email = $request->input('email');
        $spaceID = $request->input('spaceID');
        $type = $request->input('type');
        $times = json_decode($request->input('times'), false);

        foreach($times as $key => $time) {
          $booking = new Booking;
          $booking->name = $name;
          $booking->email = $email;
          $booking->spaceID = $spaceID;
          $booking->type = $type;
          $booking->day = $time->day;
          $booking->times = $time->time;
          $booking->status = 'pending';
          $booking->token = str_random(128);
          $booking->save();

          $space = Workspace::find($spaceID);

          Mail::send('emails.booking', array('name' => $name, 'email' => $email, 'type' => $type, 'day' => $time->day, 'time' => $time->time, 'token' => $token, 'space' => $space), function($message)
          {
            $message->from($email, $name);
            $message->to($space->email, $space->name)->subject('Booking: '.$type);
          });
        }



        return Response::json(['success' => 'Your request has been submitted. You will receive an email confirmation upon approval.']);
    }

    public function approve($token) {
      $booking = Booking::where('token', $token)->first();
      $booking->status = 'approved';
      $booking->save();

      $space = Workspace::find($booking->spaceID);

      Mail::send('emails.bookingApprove', array('space' => $space, 'booking' => $booking), function($message)
      {
        $message->from($space->email, $space->name);
        $message->to($booking->email, $booking->name)->subject($space->name.': Your Booking has been Approved!');
      });

      return "Booking has been approved.";
    }

    public function deny($token) {
      $booking = Booking::where('token', $token)->first();
      $booking->status = 'denied';
      $booking->save();

      $space = Workspace::find($booking->spaceID);

      Mail::send('emails.bookingDeny', array('space' => $space, 'booking' => $booking), function($message)
      {
        $message->from($space->email, $space->name);
        $message->to($booking->email, $booking->name)->subject($space->name.': Your Booking has been Denied.');
      });

      return "Booking has been denied.";
    }

}
