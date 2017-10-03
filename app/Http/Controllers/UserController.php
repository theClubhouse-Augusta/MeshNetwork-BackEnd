<?php
namespace App\Http\Controllers;
use Response;
use Purifier;
use Hash;
use Auth;
use JWTAuth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

use App\User;
use App\Userskill;
use App\Skill;

class UserController extends Controller {
  /**
   * Apply jwt middleware to specific routes.
   * @param  void
   * @return void
   */
   public function __construct() {
      $this->middleware('jwt.auth', ['only'=> [
        'updateUser',
        'delete',
        'showUser',
        'searchName',
      ]]);
    }

  /**
   * Delete user from database.
   * @param userID
   * @return  Illuminate\Support\Facades\Response::class
  */
  public function delete($id) {
    // Check for Authorized user
    $role = Auth::user()->roleID;
    return Response::json($role);

    if ($role != 1) {
      return Response::json(['error' => 'invalid credentials']);
    }
    // get user
    $user = User::find($id);
    // delete user account
    if($user->delete()) {
      return Response::json(['success' => 'Account Deleted']);
    }
    // handle database error  
    return Response::json(['error' => 'Account could not be deleted']);
  }


  /**
   * Update user in database.
   * @param Illuminate\Support\Facades\Request::class
   * @return  Illuminate\Support\Facades\Response::class
   */
  public function updateUser(Request $request) {
    //constants
    $userId = Auth::id();

    $rules = [
      'name' => 'nullable|string',
      'password' => 'nullable|string',
      'email' => 'nullable|string',
      'spaceID' => 'nullable|string',
      'company' => 'nullable|string',
      'website' => 'nullable|string',
      'bio' => 'nullable|string',
      'avatar' => 'nullable|string',
      'skills' => 'nullable|string',
      'phoneNumber' => 'nullable|string',
      'deleteSkills' => 'nullable|string',
    ];
        // Validate input against rules
    $validator = Validator::make(Purifier::clean($request->all()), $rules);

    if ($validator->fails()) {
      return Response::json(['error' => 'Invalid form input.']);
    }

    // Form Input
    $name = $request->input('name');
    $email = $request->input('email');
    $password = $request->input('password');
    $company = $request->input('company');
    $website = $request->input('website');
    $phoneNumber = $request->input('phoneNumber');
    $bio = $request->input('bio');
    // User Skills
    $skills = explode(',',$request->input('skills'));
    $deleteSkills = explode(',',$request->input('deleteSkills'));
    // Avatar Input
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
    if (!empty($email)) {
      $check = User::where('email', $email)->first();

      if (!empty($check)) {
        return Response::json(['error' => 'Email already in use']);
      }
    }
    $user = Auth::user();
    if (!empty($name)) $user->name = $name;
    if (!empty($email)) $user->email = $email;
    if (!empty($spaceID)) $user->spaceID = $spaceID;
    if (!empty($password)) $user->password = Hash::make($password);
    if (!empty($company)) $user->company = $company;
    if (!empty($website)) $user->website = $website;

    if ((!empty($phoneNumber)) 
      && (is_numeric($phoneNumber)) 
      && (count(str_split($phoneNumber)) == 7))
      {
        $user->phoneNumber = $phoneNumber;
      } 
      elseif (!empty($phoneNumber)) {
        return Response::json([ 'error' => 'Invalid phone number' ]);
      }

    if (!empty($bio)) $user->bio = $bio;
      // Profile Picture
    if (!empty($avatar)) {
      $avatarName = $avatar->getClientOriginalName();
      $avatar->move('storage/avatar/', $avatarName);
      $user->avatar = $request->root().'/storage/avatar/'.$avatarName;
    }
    // Persist changes to database
    if (!$user->save()) {
      return Response::json(['error' => 'Account not created']);
    }

    // delete skills
    if (!empty($deleteSkills)) {
      foreach ($deleteSkills as $key => $deleteSkill) {
        Userskill::where('name', $deleteSkill)->where('userID', $userId)->delete();
      }
    }

    // check for and create new skill tags
    if (!empty($skills)) {
      // create new Skills if not in database 
      foreach($skills as $key => $skill) {
        // trim white space from input
        $trimmedSkill = trim($skill);
        $checkSkill = Skill::where('name', $trimmedSkill)->first();

        if (empty($checkSkill)) {
          $newSkill = new Skill;
          $newSkill->name = $trimmedSkill;
          // Persist App\Skill to database
          if (!$newSkill->save()) {
            return Response::json([ 'error' => 'database error' ]);
          }
        }
      }
    }

    // update App\Userskill;
    foreach ($skills as $key => $skill) {
      // trim white space from input
      $trimmedSkill = trim($skill);
      // get current skill in iteration
      $skillTag = Skill::where('name', $trimmedSkill)->first();
      $checkUserSkill = Userskill::where('userID', $userId)->where('skillID', $skillTag->id)->first();;

      if (empty($checkUserSkill)) {
        // Create new UserSkill
        $userSkill = new Userskill;
        $userSkill->userID = $userId;
        $userSkill->skillID = $skillTag->id;
        $userSkill->name = $skillTag->name;
        // Persist App\Skill to database
        if (!$userSkill->save()) {
            return Response::json([ 'error' => 'database error' ]);
        }
      }
    }
    return Response::json(['success' => 'User updated successfully.']);
  }

  /**
   * Search Users by name
   * @param Illuminate\Support\Facades\Request::class
   * @return  Illuminate\Support\Facades\Response::class
  **/
  public function searchName(Request $request) {

    $rules = [
      'name' => 'required|string',
      'spaceIDs' => 'nullable|string',
    ];

    // Validate input against rules
    $validator = Validator::make(Purifier::clean($request->all()), $rules);

    if ($validator->fails()) {
      return Response::json(['error' => 'Must fill out required fields']);
    }

    // Search querys 
    $name = $request->input('name');
    $spaceIDs = explode(',', $request->input('spaceIDs'));

    if (empty($spaceIDs)) {
      $users = User::where('name', $name)->get();

      if (!empty($users)) return response::json(['success' => $users]);
      else return Response::json([ 'error' => $user.'(s) not in member database.' ]);

    } else {
      $res = array();
      foreach ($spaceIDs as $key => $spaceID) {
       $user = User::where('name', $name)->where('spaceID', $spaceID)->get(); 
       if (!empty($user)) array_push($res, $user);
      }
    }
    if (!empty($res)) return Response::json($res);
    else return Response::json([ 'error' => $name.' is not a member of selected workspaces' ]);
  }


  /** 
   * Search Users by skill/spaceid
   * @param Illuminate\Support\Facades\Request::class
   * @return  Illuminate\Support\Facades\Response::class
  **/
  public function search(Request $request) { // TODO 
    $rules = [
      'skill' => 'nullable|string',
      'spaceID' => 'nullable|string',
    ];
    // Validate input against rules
    $validator = Validator::make(Purifier::clean($request->all()), $rules);
    if ($validator->fails()) {
      return Response::json(['error' => 'Must fill out required fields']);
    }
    $spaceID = $request->input('spaceID');
    $skill = $request->input('skill');
    $spaceIDs = explode(',', $request->input('spaceID'));
  }

  /**
   * Update user in database.
   * @param void 
   * @return  Illuminate\Support\Facades\Response::class
  */
  public function showUser() {
    $id = Auth::user()->roleID;
    return Response::json($id);   
  }

  public function getSkills() {
    $skills = Skill::all();
    return Response::json($skills);
  }
}
