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
use App\Appearance;
use App\Invite;

class AppearanceController extends Controller {
  /** JWTAuth for Routes
   * @param void
   * @return void
   */
  public function __construct() {
    $this->middleware('jwt.auth', ['only' => [
      'get',
      'store',
      'show',
      'getInvite',
      'storeInvite'
    ]]);
  }

  public function store(Request $request) {
    $rules = [
      'userid' => 'required|string',
      'spaceid' => 'required|string',
      'eventid' => 'nullable|string',
      // 'work' => 'nullable|string'
    ];

    // Validate input against rules
    $validator = Validator::make(Purifier::clean($request->all()), $rules);
    if ($validator->fails()) {
      return Response::json(['error' => 'You must fill out all fields.']);
    }

    //form input
    $userID = $request->input('userID');
    $eventID = $request->input('eventID');
    $spaceID = $request->input('spaceID');
    // $work = $request->input('work');

    // create new App\Appearance
    $appearance = new Appearance;

    // required input
    $appearance->userID = $userID;
    $appearance->spaceID = $spaceID;

    // optional input
    if (!empty($eventID)) {
      $appearance->eventID = $eventID;
      $appearance->work = 0;
    }

    if(!$appearance->save()) {
      return Response::json([ 'error' => 'database error' ]);
    }
    return Response::json([ 'success' => 'Appearnace saved!' ]);
  }

  public function get() {
    return Response::json([ 'success' => Appearance::all() ]); 
  }

  // get appearances of user
  public function getCount($sort, $eventID=NULL, $spaceID=NULL) {
    /* TODO Auth
    * 
    *
    */

    /* Each of these if blocks could have diff  checks for
     * role ID
     */

    // all appearances all spaces
    if ($sort == 'countAll') {
      $appearances = count(Appearance::all());
    }

    // event.id attendance
    if ($sort == 'event') {
      $appearances = count(Appearance::where('eventID', $eventID)->get());
    }

    // overall event attendance
    if ($sort == 'eventAll') {
      $appearances = count(Appearance::where('eventID', '!=', NULL)->get());
    }

    // all appearances at workspace.id
    if ($sort == 'space') {
      $appearances = count(Appearance::where('spaceID', $spaceID)->get());
    }

    // All work appearances all spaces
    if ($sort == 'work') {
      $appearances = count(Appearance::where('work', 1)->get());
    }

    // All work appearances at space.id
    if ($sort == 'workSpace') {
      $appearances = count(Appearance::where('work', 1)->where('spaceID', $spaceID)->get());
    }

    // attendance for all events at space.id
    if ($sort == 'workSpaceAllEvents') {
      $appearances = count(Appearance::where('eventID', '!=', NULL)->where('spaceID', $spaceID)->get());
    }

    // non-event attendance at space.id
    if ($sort == 'nonEventSpace') {
      $appearances = count(Appearance::where('eventID', NULL)->where('spaceID', $spaceID)->get());
    }
  }

  // show appearances for user.id 
  public function show($userID) {
    $appearances = Appearance::where('userID', $userID)->get();
    if (empty($appearances)) {
      return Response::json([ 'error' => 'User has no appearances.' ]);
    }    
    return Response::json([ 'success' => $appearances ]);
  }

  public function storeInvite(Request $request) {
    $rules = [
      'userID' => 'required|string',
      'spaceID' => 'required|string',
      'date' => 'required|string',
    ];

    // Validate input against rules
    $validator = Validator::make(Purifier::clean($request->all()), $rules);
    if ($validator->fails()) {
      return Response::json(['error' => 'You must fill out all fields.']);
    }

    $userID = $request->input('userID');
    $spaceID = $request->input('spaceID');
    $date = $request->input('date');

    $check = Invite::where('date', $date)->first();

    if (!empty($check)) {
      return Response::json([ 'error' => 'Invite for date: '.$date.' not available ' ]);
    }

    $invite = new Invite;
    $invite->userID = $userID;
    $invite->spaceID = $spaceID;
    $invite->date = $date;

    if (!$invite->save()) {
      return Response::json([ 'error' => 'database error' ]);
    }
    return Response::json([ 'success' => 'Invite saved!' ]);
  }

  public function updateInvite($inviteID, $status) {
    $invite = Invite::where('id', $inviteID)->first();
    $invite->status = $status;

    if (!$invite->save()) {
      return Response::json([ 'error' => 'database error' ]);
    }
    return Response::json([ 'success' => 'Invite updated!' ]);
  }

  public function getInvite() {
    $userID = Auth::id();

    $invite = Invite::where('userID', $userID)->get();
    // TODO:
    // sort invites so that return value only has
    // no invite dates in past
  }
}
