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

    public function checkAuth() 
    {
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
      'workspace' => 'required|string',
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
    $workspace = $request->input('workspace');
    $findWorkSpace = Workspace::where('name', $workspace)->first();
    $spaceID = $findWorkSpace->id;
    $roleID = $request->input('roleID');
    // Optional Input
    $company = $request->input('company');
    $website = $request->input('website');
    $phoneNumber = $request->input('phoneNumber');
    $bio = $request->input('description');
    $searchOpt = $request->input('searchOpt');
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
    $user->email = $email;
    $user->spaceID = $spaceID;
    $user->roleID = 4;
    $user->searchOpt = 1;
    $user->password = Hash::make($password);
    // Optional Input
    if (!empty($company)) $user->company = $company;
    if (!empty($website)) $user->website = $website;
    
    if ((!empty($phoneNumber)) 
      && (is_numeric($phoneNumber)) 
      && (count(str_split($phoneNumber)) == 10))
      {
        $user->phoneNumber = $phoneNumber;
      } elseif (!empty($phoneNumber)) {
        return Response::json([ 'error' => 'Invalid phone number' ]);
      }

    if (!empty($bio)) $user->bio = $bio;

    // Profile Picture
    if (!empty($avatar)) {
      $avatarName = $avatar->getClientOriginalName();
      $avatar->move('storage/avatar/', $avatarName);
      $user->avatar = $request->root().'/storage/avatar/'.$avatarName;
    }
     
    // Check if user signed up as Admin
    $check_key = substr($password, 0, 8);

    // Persist user to database
    if (!$user->save()) {
      return Response::json(['error' => 'Account not created']);
    }

    $userID = $user->id;
    // Update App\Skill;  
    if (!empty($tags)) {
        foreach($tags as $key => $tag) {
            if (!property_exists($tag, 'id'))  {
                $newSkill = new Skill;
                $newSkill->name = $tag->value;
                // Persist App\Skill to database
                if (!$newSkill->save()) return Response::json([ 'error' => 'database error' ]);
            }
        }
    }

    // Update App\Eventskill;
    if (!empty($tags)) {
        foreach ($tags as $key => $tag) {
            $skillTag = Skill::where('name', $tag->value)->first();
            // Create new EventSkill
            $userSkill = new Userskill;
            $userSkill->userID = $userID;
            $userSkill->skillID = $skillTag->id;
            $userSkill->name = $skillTag->name;
            // Persist App\Skill to database
            if (!$userSkill->save())  return Response::json([ 'error' => 'eventSkill database error' ]);
            
        }
    }
    return Response::json(['success' => 'User created successfully.']);
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

        if ($validator->fails()) 
        {
            return Response::json(['error' => 'Please fill out all fields.']);
        }

        // get user input
        $email = $request->input('email');
        $password = $request->input('password');

        $user = User::where('email', $email)->first();
      
        // generate token
        $credentials = compact("email", "password");
        $token = JWTAuth::attempt($credentials);

        if ($token == false) 
        { 
            return Response::json(['error' => 'Wrong Email/Password']);
        }

        $skills = Userskill::where('userID', $user->id)
                           ->select('name')
                           ->get();
                           
        $space = Workspace::where('id', $user->spaceID)
                          ->select('name')
                          ->first();

        $events = Event::where('challenge', true)
                          ->select('title', 'id')
                          ->get();

        $attending = Calendar::where('userID', $user->id)->get();

        if (!empty($attending)) {
            $now = new DateTime();
            $upcoming = array();
            foreach ($attending as $attend)
            {
                $event = Event::find($attend->id);
                $eDate = new DateTime($event['start']);
                $diff = $now->diff($eDate);
                $formattedDiff = $diff->format('%R%a');

                if ((int)$formattedDiff > 0) 
                {
                    array_push($upcoming, 
                        [
                            "title" => $event->title,
                            "id" => $event->id 
                        ]
                    );
                }
            }
        }

        return Response::json([ 
            'user' => $user,
            'skills' => !empty($skills) ? $skills : false,
            'space' => !empty($space) ? $space : false,
            'events' => !empty($events) ? $events : false,
            'upcoming' => !empty($upcoming) ? $upcoming : false,
            'token' => $token
        ]);
    } 

  /** 
   * Get users
   * @param spaceID 
   * @return  Illuminate\Support\Facades\Response::class
  **/
    public function getUsers() 
    {
    //$rules = [
    //  'spaceIDs' => 'nullable|string',
   // ];

    //$validator = Validator::make(Purifier::clean($request->all()), $rules);

    //if ($validator->fails()) {
     // return Response::json(['error' => 'Please fill out all fields.']);
    //}
    // Ensure user has admin privalages
//    $admin = Auth::user();
  //  $id = $admin->roleID;
    //if ($id != 1) {
      //return Response::json(['error' => 'invalid credentials']);
    //}
    // get form input
    //$spaceIDs = $request->input('spaceIDs');

    // get all users if no spaceIDs specified
    //if (empty($spaceIDs)) {
      return Response::json(User::paginate(3));
   // }

    // return users by spaceID 
   // $spaceIds = explode(',', $spaceIDs);
   // $res = array(); 
    //foreach($spaceIds as $SpaceID) {
     // $users = User::where('spaceID', $SpaceID)->get();
      //if (count($users) != 0) {
        //array_push($res, $users);
     // }
   // }
    // if no users in db
   // if (empty($res)) {
     // return Response::json([ 'error' => 'No registered users in selected workspaces' ]);
   // }
   // return Response::json([ 'success' => $res ]);
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
