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
use Carbon\Carbon;
use Spatie\GoogleCalendar\Event;

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
      $resourceDays = $request->input('resourceDays');
      $resourceStartTime = $request->input('resourceStartTime');
      $resourceEndTime = $request->input('resourceEndTime');
      $resourceIncrement = $request->input('resourceIncrement');

      if(empty($resourceStartTime))
      {
        $resourceStartTime = '9:00am';
      }

      if(empty($resourceEndTime))
      {
        $resourceEndTime = '5:00pm';
      }

      if(empty($resourceDays))
      {
        $resourceDays = [1,2,3,4,5];
      }

      $auth = Auth::user();
      if($auth->spaceID != $spaceID && $auth->roleID != 2) {
        return Response::json(['error' => 'You do not have permission.']);
      }

      $res = new Resource;
      $res->spaceID = $spaceID;
      $res->resourceName = $resourceName;
      $res->resourceEmail = $resourceEmail;
      $res->resourceDays = $resourceDays;
      $res->resourceStartTime = $resourceStartTime;
      $res->resourceEndTime = $resourceEndTime;
      $res->resourceIncrement = $resourceIncrement;
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

    public function getBookings($resourceID) {
      $bookings = Booking::where('resourceID', $resourceID)->where('status', 'approved')->get();

      $bookArray = [];
      foreach($bookings as $key => $book)
      {
        $bookArray[$key]['id'] = $book->id;
        $bookArray[$key]['title'] = $book->name;
        //$bookArray[$key]['start'] = Carbon::createFromTimeStamp(strtotime($book->start))->format('Y, n, j, G, i, s');
        //$bookArray[$key]['end'] = Carbon::createFromTimeStamp(strtotime($book->end))->format('Y, n, j, G, i, s');
        $bookArray[$key]['start'] = $book->start;
        $bookArray[$key]['end'] = $book->end;
      }      

      $resource = Resource::find($resourceID);
      $resource->startTime = date("H:i:s", strtotime($resource->resourceStartTime));
      $resource->endTime = date("H:i:s", strtotime($resource->resourceEndTime));

      return Response::json(['bookings' => $bookArray, 'resource' => $resource]);

    }

    public function store(Request $request) {
        $rules = [
            'name' => 'required|string',
            'email' => 'required|string',
            'spaceID' => 'required',
            'resourceID' => 'required',
            'start' => 'required',
            'end' => 'required',
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
        //$times = json_decode($request->input('times'), false);
        $start = $request->input('start');
        $end = $request->input('end');
        $token = str_random(128);

        $start = Carbon::createFromTimeStamp(strtotime($start))->toDateTimeString();
        $end = Carbon::createFromTimeStamp(strtotime($end))->toDateTimeString();

        $user = Auth::user();
        $user = User::find($user->id);
        if($user->spaceID != $spaceID) {
          return Response::json(['error' => 'You are not a member of this space.']);
        }

        $resource = Resource::find($resourceID);

        $booking = new Booking;
        $booking->name = $name;
        $booking->email = $email;
        $booking->spaceID = $spaceID;
        $booking->resourceID = $resource->id;
        $booking->start = $start;
        $booking->end = $end;
        //$booking->day = $time->day;
        //$booking->times = $time->time;
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

        $start = Carbon::createFromTimeStamp(strtotime($start))->format('l jS \\of F Y h:i A');
        $end = Carbon::createFromTimeStamp(strtotime($end))->format('l jS \\of F Y h:i A');

        $approve = 'https://innovationmesh.com/booking/approve/'.$token;
        $deny = 'https://innovationmesh.com/booking/deny/'.$token;

        Mail::send('emails.booking', array('name' => $name, 'email' => $email, 'resource' => $resource, 'start' => $start, 'end' => $end, 'approve' => $approve, 'deny' => $deny, 'space' => $space, 'contact' => $contact),
        function($message) use ($name, $email, $resource, $start, $end, $approve, $deny, $space, $contact)
        {
          $message->from($email, $name);
          $message->to($contact, $space->name)->subject('Booking: '.$resource->resourceName);
          //$message->to('nsoharab@gmail.com', $space->name)->subject('Booking: '.$resource->resourceName);

        });

        return Response::json(['success' => 'Your request has been submitted. You will receive an email confirmation upon approval.']);
    }

    public function approve($token) {
      $booking = Booking::where('bookings.token', $token)->where('bookings.status', 'pending')->join('resources', 'bookings.resourceID', '=', 'resources.id')
      ->select('bookings.id','bookings.email', 'bookings.name', 'bookings.spaceID', 'bookings.start', 'bookings.end', 'bookings.token', 'bookings.status', 'bookings.resourceID', 'resources.resourceName', 'resources.resourceEmail')->first();
      if(!empty($booking)) {
        if(!$booking->token == 0) {
          $booking->status = 'approved';
          $booking->save();
          $space = Workspace::find($booking->spaceID);

          $booking->start = Carbon::createFromTimeStamp(strtotime($booking->start))->format('l jS \\of F Y h:i A');
          $booking->end = Carbon::createFromTimeStamp(strtotime($booking->end))->format('l jS \\of F Y h:i A');

          Mail::send('emails.bookingApprove', array('space' => $space, 'booking' => $booking),
          function($message) use ($space, $booking)
          {
            $message->from($space->email, $space->name);
            $message->to($booking->email, $booking->name)->subject($space->name.': Your Booking has been Approved!');
          });

          $space = Workspace::find($booking->spaceID);

          $resource = Resource::find($booking->resourceID);
          if($resource->resourceEmail != NULL) {
            $contact = $resource->resourceEmail;
          }
          else {
            $contact = $space->email;
          }

          $event = new Event;

          $event->name = $booking->resourceName;
          $event->startDateTime = $booking->start;
          $event->endDateTime = $booking->end;
          $event->addAttendee(['email' => $contact]);
          $event->addAttendee(['email' => $booking->email]);

          $event->save();

          return "Booking has been approved.";
        }
      } else {
        $booking = Booking::where('token', $token)->first();
        return "Booking has been ".$booking->status.".";
      }
    }

    public function deny($token) {
      $booking = Booking::where('bookings.token', $token)->where('bookings.status', 'pending')->join('resources', 'bookings.resourceID', '=', 'resources.id')
      ->select('bookings.id','bookings.email', 'bookings.name', 'bookings.spaceID', 'bookings.start', 'bookings.end', 'bookings.token', 'bookings.status', 'bookings.resourceID', 'resources.resourceName', 'resources.resourceEmail')->first();
      if(!empty($booking)) {
        if(!$booking->token == 0) {
          $booking->status = 'denied';
          $booking->save();

          $space = Workspace::find($booking->spaceID);

          $booking->start = Carbon::createFromTimeStamp(strtotime($booking->start))->format('l jS \\of F Y h:i A');
          $booking->end = Carbon::createFromTimeStamp(strtotime($booking->end))->format('l jS \\of F Y h:i A');

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
