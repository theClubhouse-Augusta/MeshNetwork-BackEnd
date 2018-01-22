<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Response;
use Purifier;
use Auth;
use JWTAuth;
use Mail;

use App\Workspace;
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
        $token = str_random(128);

        foreach($times as $key => $time) {
          $booking = new Booking;
          $booking->name = $name;
          $booking->email = $email;
          $booking->spaceID = $spaceID;
          $booking->type = $type;
          $booking->day = $time->day;
          $booking->times = $time->time;
          $booking->status = 'pending';
          $booking->token = $token;
          $booking->save();

          $space = Workspace::find($spaceID);
          $approve = 'https://innovationmesh.com/api/booking/approve/'.$token;
          $deny = 'https://innovationmesh.com/api/booking/deny/'.$token;

          Mail::send('emails.booking', array('name' => $name, 'email' => $email, 'type' => $type, 'day' => $time->day, 'time' => $time->time, 'approve' => $approve, 'deny' => $deny, 'space' => $space),
          function($message) use ($name, $email, $type, $time, $approve, $deny, $space)
          {
            $message->from($email, $name);
            $message->to($space->email, $space->name)->subject('Booking: '.$type);
            //$message->to('nsoharab@gmail.com', $space->name)->subject('Booking: '.$type);
          });
        }



        return Response::json(['success' => 'Your request has been submitted. You will receive an email confirmation upon approval.']);
    }

    public function approve($token) {
      $booking = Booking::where('token', $token)->where('status', 'pending')->first();

      if(!$booking->token == 0) {
        $booking->status = 'approved';
        $booking->token = 0;
        $booking->save();
        $space = Workspace::find($booking->spaceID);

        Mail::send('emails.bookingApprove', array('space' => $space, 'booking' => $booking),
        function($message) use ($space, $booking)
        {
          $message->from($space->email, $space->name);
          $message->to($booking->email, $booking->name)->subject($space->name.': Your Booking has been Approved!');
        });

        return "Booking has been approved.";
      } else {
        $booking = Booking::where('token', $token)->first();
        return "Booking has been ".$booking->status.".";
      }
    }

    public function deny($token) {
      $booking = Booking::where('token', $token)->where('status', 'pending')->first();
      if(!$booking->token == 0) {
        $booking->status = 'denied';
        $booking->token = 0;
        $booking->save();

        $space = Workspace::find($booking->spaceID);

        Mail::send('emails.bookingDeny', array('space' => $space, 'booking' => $booking),
        function($message) use ($space, $booking)
        {
          $message->from($space->email, $space->name);
          $message->to($booking->email, $booking->name)->subject($space->name.': Your Booking has been Denied.');
        });

        return "Booking has been denied.";
      } else {
        $booking = Booking::where('token', $token)->first();
        return "Booking has been ".$booking->status.".";
      }
    }

}
