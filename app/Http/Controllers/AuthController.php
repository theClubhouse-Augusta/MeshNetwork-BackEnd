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
use App\Userskill;
use App\Skill;

class AuthController extends Controller {

    /* JWTAuth for Routes 
    * @param void 
    * @return void 
    */
    public function __construct() {
        $this->middleware('jwt.auth', ['only' => 
            [
                'getUsers',
                'ban'
            ]
        ]);
    }

    /** SIGN UP
     * Persist user to database after sign up.
     * @param Illuminate\Support\Facades\Request::class
     * @return  Illuminate\Support\Facades\Response::class
     */
    public function signUp(Request $request) {
        // Constants
        $ADMIN_KEY = 'adminkey';
        $ORG_KEY = 'organkey';
        $RES_KEY = 'research';

        // Validation Rules
        $rules = [
            'name' => 'required|string',
            'password' => 'required|string',
            'email' => 'required|string',
            'spaceID' => 'required|string',
            'searchOpt' => 'required|string'
        ];

        // Validate input against rules
        $validator = Validator::make( Purifier::clean($request->all() ), $rules);

        if ( $validator->fails() ) {
            return Response::json(['error' => 'You must fill out all fields.']);
        }

        // Form Input
        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');
        $spaceID = $request->input('spaceID');

        // Optional Input
        $company = $request->input('company');
        $website = $request->input('website');
        $phoneNumber = $request->input('phoneNumber');
        $bio = $request->input('bio');
        $searchOpt = $request->input('searchOpt');
        $skill = $request->input('skill');
        $skills = explode(',', $skill);

        // Check for valid image upload
        if ( !empty($_FILES['avatar']) ) {

            // Check for file upload error
            if ( $_FILES['avatar']['error'] !== UPLOAD_ERR_OK ) {
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

        if ( !empty($check) ) {
            return Response::json(['error' => 'Email already in use']);
        }

        // Create new App\User;
        $user = new User;

        // Check if user signed up as Admin
        $check_key = substr($password, 0, 8);

        // assign role
        switch ($check_key) {
            case $ADMIN_KEY:
                $user->roleID = 1;
                break;

            case $ORG_KEY:
                $user->roleID = 2;
                break;

            case $RES_KEY:
                $user->roleID = 3;
                break;
            
            default: 
                $user->roleID = 4;
                break;
        }

        // Required input
        $user->name = $name;
        $user->email = $email;
        $user->spaceID = $spaceID;
        $user->searchOpt = $searchOpt;
        $user->password = Hash::make($password);

        // Optional Input
        if ( !empty($company) ) $user->company = $company;
        if ( !empty($website) ) $user->website = $website;
        if ( !empty($bio) ) $user->bio = $bio;
        
        if ( (!empty($phoneNumber) ) 
            && ( is_numeric($phoneNumber) ) 
            && ( count(str_split($phoneNumber)) == 7))
            {
                $user->phoneNumber = $phoneNumber;
            } 
            elseif ( !empty($phoneNumber) ) {
                return Response::json([ 'error' => 'Invalid phone number' ]);
            }


        // Profile Picture
        if ( !empty($avatar) ) {
            $avatarName = $avatar->getClientOriginalName();
            $avatar->move('storage/avatar/', $avatarName);
            $user->avatar = $request->root().'/storage/avatar/'.$avatarName;
        }
        

        // Persist user to database
        if ( !$user->save() ) {
            return Response::json(['error' => 'Account not created']);
        }

        // Update App\Skill;  
        if ( !empty($skill) ) {
            $newUser = User::where('email', $email)->first();

            foreach( $skills as $key => $skill ) {
                $trimmedSkill = trim($skill);
                $checkSkill = Skill::where('name', $trimmedSkill)->first();

                if ( empty($checkSkill) ) {
                    $newSkill = new Skill;
                    $newSkill->name = $trimmedSkill;
                    // Persist App\Skill to database
                    if ( !$newSkill->save() ) {
                        return Response::json([ 'error' => 'database error' ]);
                    }
                }
            }
        }

        // Update App\Userskill;
        if ( !empty($skill) ) {

            foreach ($skills as $key => $skill) {
                $trimmedSkill = trim($skill);

                // get current signed up user
                $newUser = User::where('email', $email)->first();

                // get current skill in iteration
                $skillTag = Skill::where('name', $trimmedSkill)->first();

                // Create new UserSkill
                $userSkill = new Userskill;
                $userSkill->userID = $newUser->id;
                $userSkill->skillID = $skillTag->id;
                $userSkill->name = $skillTag->name;

                // Persist App\Skill to database
                if( !$userSkill->save() ) {
                    return Response::json([ 'error' => 'database error' ]);
                }
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
        if ( $validator->fails() ) {
            return Response::json(['error' => 'Please fill out all fields.']);
        }

        // get user input
        $email = $request->input('email');
        $password = $request->input('password');
        // generate token
        $credentials = compact("email", "password");
        $token = JWTAuth::attempt($credentials);

        if ($token == false) { 
            return Response::json(['error' => 'Wrong Email/Password']);
        }
        return Response::json(['token' => $token]);
    }

    /** 
     * Get users
     * @param spaceID 
     * @return  Illuminate\Support\Facades\Response::class
     **/
    public function getUsers(Request $request) {
        $rules = [
            'spaceIDs' => 'nullable|string',
        ];

        $validator = Validator::make(Purifier::clean($request->all()), $rules);
        if ($validator->fails()) {
            return Response::json(['error' => 'Please fill out all fields.']);
        }
        // Ensure user has admin privalages
        $admin = Auth::user();
        $id = $admin->roleID;

        if ( $id != 1 ) {
            return Response::json(['error' => 'invalid credentials']);
        }

        // get form input
        $spaceIDs = $request->input('spaceIDs');
        // get all users if no spaceIDs specified

        if ( empty($spaceIDs) ) {
            return Response::json(User::all());
        }

        // return users by spaceID 
        $spaceIds = explode(',', $spaceIDs);

        $res = array(); 

        foreach($spaceIds as $SpaceID) {
            $users = User::where('spaceID', $SpaceID)->get();
            if ( count($users) != 0 ) {
                array_push($res, $users);
            }
        }
        // if no users in db
        if ( empty($res) ) {
            return Response::json([ 'error' => 'No registered users in selected workspaces' ]);
        }
        return Response::json([ 'success' => $res ]);
    }

    /**
     * Ban User
     * @param userID 
     * @return  Illuminate\Support\Facades\Response::class
     **/
    public function ban($id) {
        $admin = Auth::user();

        if ( $admin->roleID != 1 ) {
            return Response::json(['error' => 'invalid credintials']);
        }

        $bannedUser = User::where('id', $id)->first();
        $bannedUser->ban = 1;

        if ( !$bannedUser->save() ) {
            return Response::json(['error' => 'datebase error']);
        }
        return Response::json(['success' => 'user'.$bannedUser->name.' has been banned from MeshNetwork']);
    }

}