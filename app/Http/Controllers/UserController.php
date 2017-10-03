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
        // 'searchName',
        'search',
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

  /** // TODO: add check for searchOpt
   * Search Users by name
   * @param Illuminate\Support\Facades\Request::class
   * @return  Illuminate\Support\Facades\Response::class
  **/
  public function searchName(Request $request) {
    // validation rules
    $rules = [
      'name' => 'required|string',
      'spaceID' => 'nullable|string',
    ];

    // Validate input against rules
    $validator = Validator::make(Purifier::clean($request->all()), $rules);

    if ($validator->fails()) {
      return Response::json(['error' => 'Must fill out required fields']);
    }

    // Search querys 
    $name = $request->input('name');
    $spaceID = $request->input('spaceID');
    $spaceIDs = explode(',', $request->input('spaceID'));

    if (empty($spaceID)) {
      $users = User::where('name', $name)->where('searchOpt', 0)->get();

      if (count($users) != 0) return response::json(['success' => $users ]);
      else return Response::json([ 'error' => 'user: '.$name.' not in member database.' ]);

    } else {
      $res = array();
      foreach ($spaceIDs as $key => $spaceID) {
       $user = User::where('name', $name)
                ->where('spaceID', $spaceID)
                ->where('searchOpt', false)->get(); 
       if (count($user) != 0) array_push($res, $user);
      }
    }
    if (!empty($res)) return Response::json($res);
    else return Response::json([ 'error' => $name.' is not a member of selected workspaces' ]);
  }

  /** // TODO: add check for searchOpt
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
    $spaceIDs = explode(',', $request->input('spaceID'));
    $skill = $request->input('skill');

    // return users from all spaces
    if (empty($spaceID)) {
      $skills = Userskill::where('name', $skill)->select('userskills.userID')->get();
      if (count($skills) == 0) {
        return Response::json([ 'error' => 'No users found with skill' ]);
      }

      $users = array();
      foreach ($skills as $key => $skill) {
        $match = User::where('id', $skill['userID'])->where('searchOpt', false)->first();
        if (!empty($match)) {
          array_push($users, $match);
        }
      }
      return Response::json([ 'success' => $users ]);
    }

    // return users from request->input('spaceIDs');
    $users = array();
    $skills = Userskill::where('name', $skill)->select('userID')->get();     

    foreach ($skills as $key => $skill) {
      $match = User::where('id', $skill['userID'])->where('searchOpt', false)->first();
      if(!empty($match)) {
        array_push($users, $match);
      }
    }

    $res = array();
    foreach ($spaceIDs as $key => $spaceID) {
      foreach ($users as $key => $user) {
        if ($user['spaceID'] == $spaceID) {
          array_push($res, $user);
        }
      }
    }
    if (!empty($res)) {
      return Response::json([ 'success' => $res ]);
    }
    return Response::json([ 'error' => 'No users found.' ]);
  }

  /**
   * Show logged in user.
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
