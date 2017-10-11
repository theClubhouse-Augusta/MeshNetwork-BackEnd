<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Response;
use Purifier;
use Hash;
use Auth;
use JWTAuth;

use App\User;
use App\Bookable;
use App\Booking;

class BookableController extends Controller {
  /** JWTAuth for Routes
   * @param void
   * @return void
   */
  public function __construct() {
    $this->middleware('jwt.auth', ['only' => [
      'name',
      'store',
      'get',
      'getAuth',
      'getSpace',
      'show',
      'showBookable',
      'update',
      'delete',
      'storeBooking',
      'getBookings',
      'deleteBooking'
    ]]);
  }

  // create new Bookable
  public function store(Request $request) {
    //auth boilerplate
    $rules = [
      'type' => 'required|string',
      'name' => 'required|string',
      'status' => 'nullable|string'
    ];

    // Validate input against rules
    $validator = Validator::make(Purifier::clean($request->all()), $rules);

    if ($validator->fails()) {
      return Response::json(['error' => 'You must fill out all fields.']);
    }

    // form input
    $type = $request->input('type');
    $name = $request->input('name');
    // optional input
    $status = $request->input('status');

    // create new App\Bookable
    $bookable = new Bookable;
    $bookable->type = $type;
    $bookable->name = $name;
    if (!empty($status)) $bookable->status = $status;

    if (!$bookable->save()) {
      return Response::json([ 'error' => 'Database error' ]);
    }
    return Response::json([ 'success' => 'Bookable saved!' ]);
  }

  // all bookings all spaces
  public function get() {
    //auth boilerplate
    return Response::json([ 'success' => Bookable::all() ]);
  }

  // all bookings for space.id
  public function getSpace($spaceID) {

    //auth boilerplate
    $bookings->where('spaceID', $spaceID)->get();

    if (!empty($bookings)) {
      return Response::json([ 'success' => $bookings ]);
    }
    return Response::json([ 'error' => 'No reservations.' ]);
  }

  // bookings for logged in user
  public function getAuth() {
    $userID = Auth::id();
    $bookings = Booking::where('userID', $userID)->get();
    if (!empty($bookings)) {
      return Response::json([ 'success' => $bookings ]);
    }
    return Response::json([ 'error' => 'No bookings' ]);
  }

  // all bookings for user.id
  public function show($userID) {
    $bookings = Booking::where('userID', $userID)->get();
    if (!empty($bookings)) {
      return Response::json([ 'success' => $bookings ]);
    }
    return Response::json([ 'error' => 'No bookings' ]);
  }

  public function showBookable($bookablesID) {
    $bookings = Booking::where('bookablesID', $bookablesID)->get();
    if (!empty($bookings)) {
      return Response::json([ 'success' => $bookings ]);
    }
    return Response::json([ 'error' => 'No bookings' ]);
  }

  public function update($bookableID) {
    //auth boilerplate
    $rules = [
      'type' => 'nullable|string',
      'name' => 'nullable|string',
      'status' => 'nullable|string'
    ];

    // Validate input against rules
    $validator = Validator::make(Purifier::clean($request->all()), $rules);

    if ($validator->fails()) {
      return Response::json(['error' => 'You must fill out all fields.']);
    }

    // form input
    $type = $request->input('type');
    $name = $request->input('name');
    // optional input
    $status = $request->input('status');

    if (empty($status)
       && (empty($type)) 
       && (empty($name))) 
    {
      return Response::json([ 'error' => 'You must enter field to update' ]);
    }

    // create new App\Bookable
    $bookable = Bookable::where('id', $bookableID)->first();
    if (!empty($type)) $bookable->type = $type;
    if (!empty($name)) $bookable->name = $name;
    if (!empty($status)) $bookable->status = $status;

    if (!$bookable->save()) {
      return Response::json([ 'error' => 'Database error' ]);
    }
    return Response::json([ 'success' => 'Bookable saved!' ]);


  }

  public function updateBooking() {
    // auth boilerplate
    $rules = [
      'bookingID' => 'required|string',
      'start' => 'nullable|string',
      'end' => 'nullable|string'
    ];

    // Validate input against rules
    $validator = Validator::make(Purifier::clean($request->all()), $rules);

    if ($validator->fails()) {
      return Response::json(['error' => 'You must fill out all fields.']);
    }

    // authenticated user
    $userID = Auth::id();
    $spaceID = Auth::user()->spaceID;
    // form input
    $bookaingID = $request->input('bookingID');
    $start = $request->input('start');
    $end = $request->input('end');

    // TODO: date checking
    // $check = Bookings::where('bookablesID', $booking)
                      // ->where('start' not $start or $end)
                      // ->where('end', not $start or $end)
                      // ->first();

    if (!empty($check)) {
      $bookable = Bookable::where('id', 'bookablesID')->first();
      return Response::json([ 'error' => $bookable->name.' is not available on selected dates.' ]);
    }                  

    if ( empty($start) && empty($end) ) {
      return Response::json([ 'error' => 'You must enter field to update' ]);
    }

    // create new booking reservation
    $booking = Booking::where('id', $bookingID)->first();
    // $booking->bookablesID = $bookablesID;
    if (!empty($start)) $booking->start = $start;
    if (!empty($end)) $booking->end = $end;

    if (!$booking->save()) {
      return Response::json([ 'error' => 'database error' ]); 
    }
    return Response::json([ 'success' => 'Reserved bookable!' ]);

  }

  public function delete($id) {
    // auth boilerplate
    $bookable = Bookable::where('id', $id)->first();
    if (!$bookable->delete()) {
      return Response::json([ 'error' => 'database error' ]);
    }
    return Response::json([ 'success' => 'Bookable deleted' ]);
  }

  public function storeBooking() {
    $rules = [
      'bookablesID' => 'required|bookablesID',
      'start' => 'required|string',
      'end' => 'required|string'
    ];

    // Validate input against rules
    $validator = Validator::make(Purifier::clean($request->all()), $rules);

    if ($validator->fails()) {
      return Response::json(['error' => 'You must fill out all fields.']);
    }

    // authenticated user
    $userID = Auth::id();
    $spaceID = Auth::user()->spaceID;
    // form input
    $bookablesID = $request->input('bookablesID');
    $start = $request->input('start');
    $end = $request->input('end');

    // TODO: date checking
    // $check = Bookings::where('bookablesID', $booking)
                      // ->where('start' not $start or $end)
                      // ->where('end', not $start or $end)
                      // ->first();

    if (!empty($check)) {
      $bookable = Bookable::where('id', 'bookablesID')->first();
      return Response::json([ 'error' => $bookable->name.' is not available on selected dates.' ]);
    }                  

    // create new booking reservation
    $booking = new Booking;
    $booking->bookablesID = $bookablesID;
    $booking->start = $start;
    $booking->end = $end;

    if (!$booking->save()) {
      return Response::json([ 'error' => 'database error' ]); 
    }
    return Response::json([ 'success' => 'Reserved bookable!' ]);

  }

  public function getBookings() {
    // auth boilerplate
    return Response::json([ 'success' => Booking::all() ]);            
  }

  public function deleteBooking($id) {
    $booking = Booking::where('id', $id)->first();

    if (empty($booking)) {
      return Response::json([ 'error' => 'No booking with id' ]);
    }

    if (!$booking->delete()) {
      return Response::json([ 'error' => 'database error' ]);
    }
    return Response::json([ 'success' => 'Booking deleted' ]);
  }
}
