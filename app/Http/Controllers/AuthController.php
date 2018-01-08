<?php
namespace App\Http\Controllers;

use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Response;
use Purifier;
use Hash;
use Auth;
use JWTAuth;

use App\User;
use App\Userskill;
use App\Skill;
use App\Workspace;
use App\Event;
use App\Eventdate;
use App\Calendar;

class AuthController extends Controller {
    /* JWTAuth for Routes
     * @param void
     * @return void
    */
    public function __construct() {
        $this->middleware('jwt.auth', ['only' => [
            // 'getUsers',
            'ban',
            'checkAuth',
        ]]);
    }

    public function checkAuth() {
        return Response::json(Auth::check());
    }

    /** SIGN UP
     * Persist user to database after sign up.
     * @param Illuminate\Support\Facades\Request::class
     * @return  Illuminate\Support\Facades\Response::class
     */
    public function signUp(Request $request) {
        // Validation Rules
        $rules = [
            'name' => 'required|string',
            'password' => 'required|string',
            'email' => 'required|string',
            'spaceID' => 'required|string',
            'plan' => 'nullable|string',
            'tags' => 'nullable|string',
        ];
        // Validate input against rules
        $validator = Validator::make(Purifier::clean($request->all()), $rules);

        if ($validator->fails()) {
            return Response::json(['error' => 'You must fill out all fields.']);
        }
        // Form Input
        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');
        $spaceID = $request->input('spaceID');
        $bio = $request->input('bio');
        // $workspace = $request->input('workspace');
        // $space = Workspace::where('name', $workspace)->first();
        // $spaceID = $space->id;
        $tags = json_decode($request->input('tags'));

        // Check for valid image upload
        if (!empty($_FILES['avatar'])) {
        // Check for file upload error
          if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
              return Response::json([ "error" => "Upload failed with error code " . $_FILES['avatar']['error']]);
          }
        // checks for valid image upload
          $info = getimagesize($_FILES['avatar']['tmp_name']);

          if ($info === FALSE) {
            return Response::json([ "error" => "Unable to determine image type of uploaded file" ]);
          }

        // checks for valid image upload
          if (($info[2] !== IMAGETYPE_GIF) 
                && ($info[2] !== IMAGETYPE_JPEG) 
                && ($info[2] !== IMAGETYPE_PNG)) 
            {
                return Response::json([ "error" => "Not a gif/jpeg/png" ]);
            }

        // Get profile image input
          $avatar = $request->file('avatar');
        }

        // Ensure unique email
        $check = User::where('email', $email)->first();

        if (!empty($check)) {
            return Response::json(['error' => 'Email already in use']);
        }

        // Create new App\User;
        $user = new User;
        // Required input
        $user->name = $name;
        $user->bio = $bio;
        $user->email = $email;
        if (!empty($spaceID)) $user->spaceID = $spaceID;
        $user->roleID = 4;
        $user->password = Hash::make($password);
        

        // if (!empty($bio)) $user->bio = $bio;

        // Profile Picture
        if (!empty($avatar)) {
          $avatarName = $avatar->getClientOriginalName();
          $avatar->move('storage/avatar/', $avatarName);
          $user->avatar = $request->root().'/storage/avatar/'.$avatarName;
        }

        $space = Workspace::find($spaceID)->makeVisible('stripe');
        $key = $space->stripe;
        \Stripe\Stripe::setApiKey($key);

        $plan = $request['plan'];
        $cardToken = $request['customerToken'];

        if (!empty($plan)) {
            // create customer
            $customer = \Stripe\Customer::create(array(
                "source" => $cardToken, // obtained with Stripe.js
                "email" => $email
            ));

            // subscription
            \Stripe\Subscription::create(array(
                "customer" => $customer['id'],
                "items" => array(
                    array(
                        "plan" => $plan,
                    ),
                )
            ));
        }
        
        // Persist user to database
        $success = $user->save();
        if (!$success) {
            return Response::json(['error' => 'Account not created']);
        }

        $userID = $user->id;
        // Update App\Skill;  
        if (!empty($tags)) {
            foreach($tags as $key => $tag) {
                if (!property_exists($tag, 'id'))  {
                    $check = Skill::where('name', $tag->value)->first();
                    if (empty($check)) {
                        $newSkill = new Skill;
                        $newSkill->name = $tag->value;
                        // Persist App\Skill to database
                        $success = $newSkill->save();
                        if (!$success) return Response::json([ 'error' => 'database error' ]);
                    }
                }
            }
        }

        // Update App\Userskill;
        if (!empty($tags)) {
            foreach ($tags as $key => $tag) {
                $skillTag = Skill::where('name', $tag->value)->first();
                // Create new EventSkill
                $userSkill = new Userskill;
                $userSkill->userID = $userID;
                $userSkill->skillID = $skillTag->id;
                $userSkill->name = $skillTag->name;
                // Persist App\Skill to database
                $success = $userSkill->save();
                if (!$success) return Response::json([ 'error' => 'eventSkill database error' ]);
            }
        }

        $credentials = compact("email", "password");
        $token = JWTAuth::attempt($credentials);
        return Response::json([
            'id' => $user->id,
            'token' => $token
        ]);
    }


    /**
     * Sign In
     *
     * @param Illuminate\Support\Facades\Request::class
     * @return  Illuminate\Support\Facades\Response::class
    */
    public function signIn(Request $request) {
        // required input
        $rules = [
          'email' => 'required',
          'password' => 'required'
        ];
        
        // Validate and purify input 
        $validator = Validator::make(Purifier::clean($request->all()), $rules);

        if ($validator->fails()) {
            return Response::json(['error' => 'Please fill out all fields.']);
        }

        $password = $request->input('password');
        // get user input
        $email = $request->input('email');
        $password = $request->input('password');

        $user = User::where('email', $email)->first();
      
        // generate token
        $credentials = compact("email", "password");
        $token = JWTAuth::attempt($credentials);

        if ($token == false) { 
            return Response::json(['error' => 'Wrong Email/Password']);
        }

        $skills = Userskill::where('userID', $user->id)
                           ->select('name')
                           ->get();
                           
        $space = Workspace::where('id', $user->spaceID)
                          ->select('name')
                          ->first();

        $events = $this->getUpcomingEvents();
        $upcoming = $this->getAttendingEvents($user->id);

        return Response::json([
            'user' => $user,
            'skills' => !empty($skills) ? $skills : false,
            'space' => !empty($space) ? $space : false,
            'events' => !empty($events) ? $events : false,
            'upcoming' => !empty($upcoming) ? $upcoming : false,
            'token' => $token,
        ]);
    } 

    private function getUpcomingEvents() {
        $now = new DateTime();
        $eventdates = Eventdate::where('start', '>', $now->format('Y-m-d'))->get();

        $eventIDs = array();
        foreach ($eventdates as $key => $event) {
            if ($key == 0) {
                $id = $event->eventID;
                array_push($eventIDs, $id);
            }
            if ($key != 0) {   
                $check = $event->eventID;
                if ($id != $check) {
                    $id = $check;
                    array_push($eventIDs, $id);
                }
            }
        }
        $events = array();
        foreach ($eventIDs as $id) {
            $event = Event::find($id);
            array_push($events, $event);
        }
        return $events;

    }

    private function getAttendingEvents($userID) {
        $now = new DateTime();
        $attending = Calendar::where('userID', $userID)->get();
        $upcoming = array();
        if (!empty($attending)) {
            foreach ($attending as $attend) {
                $eventdate = Eventdate::where('eventID', $attend->eventID)->first();
                if (!empty($eventdate)) {
                    $event = Event::find($attend->eventID);
                    $title = $event->title;
                    $id = $event->id;
                    $eDate = new DateTime($eventdate->start);
                    $diff = $now->diff($eDate);
                    $formattedDiff = $diff->format('%R%a');

                    if ((int)$formattedDiff > 0) {
                        array_push($upcoming, 
                            [
                                "title" => $title,
                                "id" => $id
                            ]
                        );
                    }
                }
            }
        }
        return $upcoming;
    }

  /** 
   * Get users
   * @param spaceID 
   * @return  Illuminate\Support\Facades\Response::class
  **/
    public function getUsers() {
        $organizer = Auth::user();             
        if ($organizer->roleID != 2) {
            return Response::json([ 'error' => 'invalid role' ]);
        }

        $users = User::where('spaceID', $organizer->spaceID)->get();

        if (!empty($users)) {
            return Response::json($users);
        }   else {
            return Response::json([ 'error' => 'no users for space' ]);
        }
    }

  /**
   * Ban User
   * @param userID 
   * @return  Illuminate\Support\Facades\Response::class
  **/
  public function ban($id) {
    $admin = Auth::user();
    

    if ($admin->roleID != 1) {
        return Response::json(['error' => 'invalid credintials']);
    }
    $bannedUser = User::where('id', $id)->first();
    $bannedUser->ban = 1;

    if (!$bannedUser->save()) {
      return Response::json(['error' => 'datebase error']);
    }
    return Response::json(['success' => 'user'.$bannedUser->name.' has been banned from MeshNetwork']);
  }

}
