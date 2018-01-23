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
use App\Resource;
use App\User;

class BookingController extends Controller {
    public function __construct() {
      $this->middleware('jwt.auth', ['only' => [
          'store',
          'storeResource',
          'deleteResource'
      ]]);
    }

    public function getResources($spaceID) {
      $resources = Resource::where('spaceID', $spaceID)->get();

      return Response::json($resources);
    }

    public function storeResource(Request $request) {
      $rules = [
        'spaceID' => 'required',
        'resourceName' => 'required'
      ];

      $validator = Validator::make(Purifier::clean($request->all()), $rules);

      if ($validator->fails()) {
          return Response::json(['error' => 'Please fill out all fields.']);
      }

      $spaceID = $request->input('spaceID');
      $resourceName = $request->input('resourceName');
      $resourceEmail = $request->input('resourceEmail');

      $auth = Auth::user();
      if($auth->spaceID != $spaceID && $auth->roleID != 2) {
        return Response::json(['error' => 'You do not have permission.']);
      }

      $res = new Resource;
      $res->spaceID = $spaceID;
      $res->resourceName = $resourceName;
      $res->resourceEmail = $resourceEmail;
      $res->save();

      $resourceData = Resource::find($res->id);

      return Response::json(['success' => 'Resource Added.', 'resource' => $resourceData]);
    }

    public function deleteResource($id)
    {
      $resource = Resource::find($id);

      $auth = Auth::user();
      if($auth->spaceID != $resource->spaceID && $auth->roleID != 2) {
        return Response::json(['error' => 'You do not have permission.']);
      }

      $resource->delete();

      return Response::json(['success' => 'Resource Deleted.']);
    }

    public function getTimes($spaceID)
    {
      $resources = Resource::all();

    }

    public function store(Request $request) {
        $rules = [
            'name' => 'required|string',
            'email' => 'required|string',
            'spaceID' => 'required|string',
            'resourceID' => 'required',
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
        $resourceID = $request->input('resourceID');
        $times = json_decode($request->input('times'), false);
        $token = str_random(128);

        $user = Auth::user();
        $user = User::find($user->id);
        if($user->spaceID != $spaceID) {
          return Response::json(['error' => 'You are not a member of this space.']);
        }

        $resource = Resource::find($resourceID);

        foreach($times as $key => $time) {
          $booking = new Booking;
          $booking->name = $name;
          $booking->email = $email;
          $booking->spaceID = $spaceID;
          $booking->resourceID = $resource->id;
          $booking->day = $time->day;
          $booking->times = $time->time;
          $booking->status = 'pending';
          $booking->token = $token;
          $booking->save();

          $space = Workspace::find($spaceID);
          if($resource->resourceEmail != NULL) {
            $contact = $resource->resourceEmail;
          }
          else {
            $contact = $space->email;
          }

          $approve = 'https://innovationmesh.com/api/booking/approve/'.$token;
          $deny = 'https://innovationmesh.com/api/booking/deny/'.$token;

          Mail::send('emails.booking', array('name' => $name, 'email' => $email, 'resource' => $resource, 'day' => $time->day, 'time' => $time->time, 'approve' => $approve, 'deny' => $deny, 'space' => $space, 'contact' => $contact),
          function($message) use ($name, $email, $resource, $time, $approve, $deny, $space, $contact)
          {
            $message->from($email, $name);
            $message->to($contact, $space->name)->subject('Booking: '.$resource->resourceName);
            //$message->to('nsoharab@gmail.com', $space->name)->subject('Booking: '.$type);
          });
        }



        return Response::json(['success' => 'Your request has been submitted. You will receive an email confirmation upon approval.']);
    }

    public function approve($token) {
      $booking = Booking::where('bookings.token', $token)->where('bookings.status', 'pending')->join('resources', 'bookings.resourceID', '=', 'resources.id')
      ->select('bookings.id','bookings.email', 'bookings.name', 'bookings.spaceID', 'bookings.day', 'bookings.times', 'bookings.token', 'bookings.status', 'bookings.resourceID', 'resources.resourceName', 'resources.resourceEmail')->first();
      if(!empty($booking)) {
        if(!$booking->token == 0) {
          $booking->status = 'approved';
          $booking->save();
          $space = Workspace::find($booking->spaceID);

          Mail::send('emails.bookingApprove', array('space' => $space, 'booking' => $booking),
          function($message) use ($space, $booking)
          {
            $message->from($space->email, $space->name);
            $message->to($booking->email, $booking->name)->subject($space->name.': Your Booking has been Approved!');
          });

          return "Booking has been approved.";
        }
      } else {
        $booking = Booking::where('token', $token)->first();
        return "Booking has been ".$booking->status.".";
      }
    }

    public function deny($token) {
      $booking = Booking::where('bookings.token', $token)->where('bookings.status', 'pending')->join('resources', 'bookings.resourceID', '=', 'resources.id')
      ->select('bookings.id','bookings.email', 'bookings.name', 'bookings.spaceID', 'bookings.day', 'bookings.times', 'bookings.token', 'bookings.status', 'bookings.resourceID', 'resources.resourceName', 'resources.resourceEmail')->first();
      if(!empty($booking)) {
        if(!$booking->token == 0) {
          $booking->status = 'denied';
          $booking->save();

          $space = Workspace::find($booking->spaceID);

          Mail::send('emails.bookingDeny', array('space' => $space, 'booking' => $booking),
          function($message) use ($space, $booking)
          {
            $message->from($space->email, $space->name);
            $message->to($booking->email, $booking->name)->subject($space->name.': Your Booking has been Denied.');
          });

          return "Booking has been denied.";
        }
      } else {
        $booking = Booking::where('token', $token)->first();
        return "Booking has been ".$booking->status.".";
      }
    }

}
