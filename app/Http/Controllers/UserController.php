<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Response;
use \Mews\Purifier\Facades\Purifier;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DateTime;

use App\User;
use App\Appearance;
use App\Role;
use App\Calendar;
use App\Userskill;
use App\Skill;
use App\Event;
use App\Eventdate;
use App\Workspace;
use App\Services\InputValidator;
use App\Services\Stripe\SubscriptionService;

class UserController extends Controller
{
    private  $inputValidator;
    /**
     * Apply jwt middleware to specific routes.
     * @param  void
     * @return void
     */
    public function __construct(InputValidator $inputValidator) {
        $this->inputValidator = $inputValidator;
        $this->middleware('jwt.auth', ['only' => [
            'updateUser',
            'delete',
            'makeOrganizer',
            'getDashboardUsers',
            'updateCustomerMeshEmail'
//            'showUser',
           //'user',
            //'searchName',
           //'search',
           //'userSkills',
        //   'getSkills',
            // 'allSkills',
            // 'Organizers'
        ]]);
    }

    public function updateCustomerMeshEmail(Request $request) {
        $user = Auth::user();
        $spaceID = $user->spaceID;
        $validInput = $this->inputValidator::validateUpdateCustomerMeshEmail($request);
        if (!$validInput['isValid']) {
            return Response::json(['error' => $validInput['message']]);
        }
        $space = Workspace::find($spaceID)->makeVisible('stripe');
        $subscriptionService = new SubscriptionService($space->stripe);
        $request['spaceID'] = $spaceID;
        $success = $subscriptionService->updateCustomerMeshEmail($request);
        if ($success) {
            return Response::json(['emailUpdated' => $success ]);
        } else {
            return Response::json(['error' => 1 ]);
        }
    }

    /**
     * Delete user from database.
     * @param userID
     * @return  Illuminate\Support\Facades\Response::class
     */
    public function delete($id)
    {
        // Check for Authorized user
        $role = Auth::user()->roleID;
        return Response::json($role);

        if ($role != 1) {
            return Response::json(['error' => 'invalid credentials']);
        }
        // get user
        $user = User::find($id);
        // delete user account
        if ($user->delete()) {
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
    public function updateUser(Request $request)
    {
        //constants
        $rules = [
          // userInfo
            'name' => 'nullable|string',
            'title' => 'nullable|string',
            'avatar' => 'nullable|string',
            'password' => 'nullable|string',
            'passwordConfirm' => 'nullable|string',
          //'email' => 'nullable|string',
            'skills' => 'nullable|string',
            'phoneNumber' => 'nullable|string',
            'facebook' => 'nullable|string',
            'twitter' => 'nullable|string',
            'instagram' => 'nullable|string',
            'linkedin' => 'nullable|string',
            'github' => 'nullable|string',
            'behance' => 'nullable|string',
        ];

        // Validate input against rules
        $validator = Validator::make(Purifier::clean($request->all()), $rules);

        if ($validator->fails()) {
            return Response::json(['error' => 'Invalid form input.']);
        }

        $name = $request->input('name');
        $title = $request->input('title');
        //$email = $request->input('email');
        $password = $request->input('password');
        $passwordConfirm = $request->input('passwordConfirm');
        $phoneNumber = $request->input('phoneNumber');
        $facebook = $request->input('facebook');
        $twitter = $request->input('twitter');
        $instagram = $request->input('instagram');
        $linkedin = $request->input('linkedin');
        $github = $request->input('github');
        $behance = $request->input('behance');
        $skills = json_decode($request->input('skills'), true);

        $skillArray = [];
        foreach ($skills as $key => $skill) {
            $skillArray[] = $skill;
        }

        $skills = implode(",", $skillArray);

        if ($password != $passwordConfirm) {
            return Response::json(['error' => 'Passwords do not match.']);
        }


        // Avatar Input
        if (!empty($_FILES['avatar'])) {
            // Check for file upload error
            if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
                return Response::json(["error" => "Upload failed with error code " . $_FILES['avatar']['error']]);
            }
            // checks for valid image upload
            $info = getimagesize($_FILES['avatar']['tmp_name']);

            if ($info === false) {
                return Response::json(["error" => "Unable to determine image type of uploaded file"]);
            }

            if (($info[2] !== IMAGETYPE_GIF)
                && ($info[2] !== IMAGETYPE_JPEG)
                && ($info[2] !== IMAGETYPE_PNG)) {
                return Response::json(["error" => "Not a gif/jpeg/png"]);
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
        $user = User::find($user->id);

        // userInfo
        if (!empty($name)) {$user->name = $name;} else { $user->name = $user->name; }
        //if (!empty($website)) {$user->website = $website;} else { $user->website = $user->website; }
        if (!empty($title)) {$user->title = $title;} else { $user->title = $user->title; }
        // workInfo
        //if (!empty($email)) $user->email = $email;
        if (!empty($phoneNumber)) { $user->email = $phoneNumber; } else { $user->email = $user->email; }
        if (!empty($password)) { $user->password = Hash::make($password); } else { $user->password = $user->password; }
        if (!empty($facebook)) { 
            if($facebook == "null") {
                $user->facebook = NULL;
            } 
            else {
                $user->facebook = $facebook;
            }
        }
        else { 
            $user->facebook = $user->facebook; 
        }
        if (!empty($twitter)) { 
            if($twitter == "null") {
                $user->twitter = NULL;
            } 
            else {
                $user->twitter = $twitter; 
            }
        } 
        else { 
            $user->twitter = $user->twitter; 
        }
        if (!empty($instagram)) { 
            if($instagram == "null") {
                $user->instagram = NULL;
            }
            else {
                $user->instagram = $instagram; 
            }
        } 
        else { 
            $user->instagram = $user->instagram; 
        }
        if (!empty($linkedin)) { 
            if($linkedin == "null") {
                $user->linkedin = NULL;
            } 
            else {
                $user->linkedin = $linkedin; 
            }
        } 
        else { 
            $user->linkedin = $user->linkedin; 
        }
        if (!empty($github)) { 
            if($github == "null") {
                $user->github = NULL;
            } 
            else {
                $user->github = $github; 
            }
        } else { 
            $user->github = $user->github; 
        }
        if (!empty($behance)) { 
            if($behance == "null") {
                $user->behance = NULL;
            }
            else {
                $user->behance = $behance; 
            }
        } else { 
            $user->behance = $user->behance; 
        }
        if (!empty($skills)) { 
            if($skills == "")
            {
                $user->skills = NULL;
            }
            else {
                $user->skills = $skills; 
            }
        } else { 
            $user->skills = $user->skills; 
        }

        if (!empty($bio)) {$user->bio = $bio;} else { $user->bio = $user->bio; }
        // Profile Picture
        if (!empty($avatar)) {
            $avatarName = $avatar->getClientOriginalName();
            $avatar->move('storage/avatar/', $avatarName);
            $user->avatar = $request->root() . '/storage/avatar/' . $avatarName;
        }

        $user->save();

        /*// delete skills
        if (!empty($deleteSkills)) {
            foreach ($deleteSkills as $key => $deleteSkill) {
                Userskill::where('name', $deleteSkill)->where('userID', $userID)->delete();
            }
        }

        // check for and create new skill tags
        if (!empty($tags)) {
            foreach($tags as $key => $tag) {
                if (!property_exists($tag, 'id'))  {
                    $check = Skill::where('name', $tag->value)->first();
                    return Response::json($check);
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

        // update App\Userskill;

        if (!empty($tags)) {
            foreach($tags as $key => $skill) {
                // get current skill in iteration
                $skillTag = Skill::where('name', $skill)->first();
                $checkUserSkill = Userskill::where('userID', $userID)
                                    ->where('skillID', $skillTag->id)
                                    ->first();

                if (empty($checkUserSkill)) {
                    // Create new UserSkill
                    $userSkill = new Userskill;
                    $userSkill->userID = $userID;
                    $userSkill->skillID = $skillTag->id;
                    $userSkill->name = $skillTag->name;

                    if (!$userSkill->save()) {
                        return Response::json([ 'error' => 'database error' ]);
                    }
                }
            }
        }*/
        return Response::json(['success' => 'Account updated!']);
    }

    /**
     * Search Users by skill/spaceid
     * @param Illuminate\Support\Facades\Request
     * @return  Illuminate\Support\Facades\Response
     **/
    public function search(Request $request)
    {
        // url query params
        $query = $request->input('query');
        $tag = $request->input('tag');

        if (!empty($tag)) {
            $tag = Skill::find($tag);
            $users = User::where('skills', 'LIKE', '%' . $tag->name . '%')
                ->orderBy('created_at', 'DESC')
                ->get();

            if ($users->isEmpty()) {
                return Response::json(['error' => 'No Users Found.']);
            }

            return Response::json($users);
        } else {
            $users = User::where('name', 'LIKE', '%' . $query . '%')
                ->orWhere('title', 'LIKE', '%' . $query . '%')
                ->where('skills', 'LIKE', '%' . $query . '%')
                ->orderBy('created_at', 'DESC')
                ->get();

            if ($users->isEmpty()) {
                return Response::json(['error' => 'No Users Found.']);
            }

            return Response::json($users);
        }
        // handle skill tag button click
      /*  if (!empty($tag)) {
            $skills = Userskill::where('name', $tag)->select('userskills.userID')->distinct('userID')->get();
            if (count($skills) == 0) {
                return Response::json([ 'error' => 'No users found with skill' ]);
            }

            $users = array();
            foreach ($skills as $key => $skill)  {
                $match = User::where('id', $skill['userID'])->first();
                if (!empty($match)) {
                  array_push($users, $match);
                }
            }

            if (!empty($users)) {
                return Response::json($users);
            }   else {
                return Response::json([ 'error' => 'no user matched tag' ]);
            }
        }*/

        // handle search input query
        /*$users = User::where('name', 'LIKE', '%'.$query.'%')
                    ->Orwhere('bio', 'LIKE', '%'.$query.'%')
                    ->Orwhere('email', 'LIKE', '%'.$query.'%')
                    ->get();
        $skills = Userskill::where('name', 'LIKE', '%'.$query.'%')
                           ->select('userskills.userID')
                           ->get();

        // App\Skill match and App\User match
        if ( count($skills) != 0 && count($users) != 0) {
            $res = array();
            array_push($res, $users);
            foreach ($skills as $key => $skill) {
                $match = User::where('id', $skill['userID'])->first();
                if (!empty($match)) {
                    array_push($res, $match);
                }
            }

            return $res;
        }

        // App\Skill match
        if ( count($users) == 0 && count($skills) != 0 )
        {
            $res = array();
            foreach ($skills as $key => $skill)
            {
                $match = User::where('id', $skill['userID'])->first();

                if (!empty($match))
                {
                    array_push($res, $match);
                }
            }
            return Response::json($res);
        }

        // App\User match
        if ( count($users) != 0 && count($skills) == 0 )
        {
            $res = array();
            foreach ($users as $user)
            {
                array_push($res, $user);
            }
            return Response::json($res);
        }
        return Response::json(['error' => 'nothing matched query']);*/
    }


    /**
     * Show logged in user.
     * @param void
     * @return  Illuminate\Support\Facades\Response::class
     */
    public function showUser(Request $request)
    {
        $user = Auth::user();
        $id = Auth::id();

        $skills = Userskill::where('userID', $user->id)
            ->select('name')
            ->get();
        $space = Workspace::where('id', $user->spaceID)
            ->select('name')
            ->first();
        $events = $this->getUpcomingEvents();
        $upcoming = $this->getAttendingEvents($user->id);

        if (empty($user)) {
            return Response::json(['error' => 'User does not exist']);
        }

        return Response::json([
            'user' => $user,
            'skills' => !empty($skills) ? $skills : false,
            'space' => !empty($space) ? $space : false,
            'events' => !empty($events) ? $events : false,
            'upcoming' => !empty($upcoming) ? $upcoming : false,
        ]);
    }

    private function getUpcomingEvents()
    {
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

    private function getAttendingEvents($userID)
    {
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
                        array_push(
                            $upcoming,
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


    public function allSkills()
    {
        $skills = Skill::all();
        $skillArray = [];
        foreach ($skills as $skill) {
            array_push($skillArray, $skill->name);
        }
        return Response::json($skillArray);
    }

    public function getSkills()
    {
        $userskills = DB::table('userskills')
            ->select(DB::raw('COUNT(*) AS foo, skillID'))
            ->groupBY('skillID')
            ->orderBy('foo', 'desc')
            ->limit(12)
            ->get();

        $res = array();
        foreach ($userskills as $userskill) {
            array_push($res, Skill::find($userskill->skillID));
        }

        if (count($res) == 0) {
            $res = Skill::take(12)->get();
        }
        return Response::json($res);
    }

    public function userSkills()
    {
        $userID = Auth::id();
        $skills = Userskill::where('userID', $userID)->get();
        $skillsArray = array();
        foreach ($skills as $skill) {
            array_push($skillsArray, [
                'label' => $skill->name,
                'value' => $skill->name,
                'id' => $skill->id
            ]);
        }
        return Response::json($skillsArray);
    }

    public function user($id)
    {
        $user = User::find($id);
        if (empty($user)) {
            return Response::json(['error' => 'user not found']);
        }
        /*$skills = Userskill::where('userID', $id)
                           ->select('name')
                           ->get();*/

        $skills = $user->skills;
        if ($skills == null || strlen($skills) == 0 || $skills == "") {
            $skills = [];
        } else {
            $skills = explode(",", $skills);
        }

        $space = Workspace::where('id', $user->spaceID)
            ->select('name')
            ->first();

        $events = $this->getUpcomingEvents();
        $upcoming = $this->getAttendingEvents($user->id);

        if (empty($user)) {
            return Response::json(['error' => 'User does not exist']);
        }
        return Response::json([
            'user' => $user,
            'skills' => !empty($skills) ? $skills : false,
            'space' => !empty($space) ? $space : false,
            'events' => !empty($events) ? $events : false,
            'upcoming' => !empty($upcoming) ? $upcoming : false,
        ]);
    }

    public function OrganizersForEvents()
    {
        $organizers = User::all();
        $organizersArray = [];
        foreach ($organizers as $organizer) {
            array_push($organizersArray, $organizer->email);
        }
        return Response::json($organizersArray);
    }
    public function Organizers()
    {
        $organizers = User::all();
        $organizersArray = [];
        foreach ($organizers as $organizer) {
            array_push($organizersArray, [
                'label' => $organizer->name . ' - ' . $organizer->email,
                'value' => $organizer->id,
                'avatar' => $organizer->avatar,
                'name' => $organizer->name
            ]);
        }
        return Response::json($organizersArray);
    }

    public function usersFromSpace($spaceID)
    {
        $users = User::where('spaceID', $spaceID)->get();
        $usersArray = [];
        foreach ($users as $user) {
            $email = substr($user->email, 0, 3).'****'.substr($user->email, strpos($user->email, "@"));
            array_push($usersArray, [
                'label' => $user->name . ' - ' . $email,
                'value' => $user->id,
                'avatar' => $user->avatar,
                'name' => $user->name
            ]);
        }
        return Response::json(['users' => $usersArray]);
    }

    public function getKioskUsers()
    {
        $users = User::all();
        $usersArray = [];
        foreach ($users as $user) {
            $email = substr($user->email, 0, 3).'****'.substr($user->email, strpos($user->email, "@"));
            array_push($usersArray, [
                'label' => $user->name . ' - ' . $email,
                'value' => $user->id,
                'avatar' => $user->avatar,
                'name' => $user->name
            ]);
        }
        return Response::json($usersArray);
    }



    public function getSpaceUsers($spaceID)
    {
        $space = Workspace::where('id', $spaceID)->orWhere('slug', $spaceID)->first();

        $users = User::where('spaceID', $space->id)->get();

        return Response::json($users);
    }

    public function makeOrganizer($userID)
    {
        $organizer = Auth::user();
        $user = User::find($userID);
        if ($organizer->roleID != 2 || $user->spaceID != $organizer->spaceID) {
            return Response::json(['error' => 'invalid credentials']);
        }

        $user = User::find($userID);
        if ($user->roleID == 2) {
            $user->roleID = 3;
        } else {
            $user->roleID = 2;
        }

        $success = $user->save();
        if ($success) {
            return Response::json(['success' => 'account updated successfully']);
        } else {
            return Response::json(['error' => 'database error']);
        }
    }

    public function getDashboardUsers($id)
    {
        $auth = Auth::user();
        if($auth->spaceID != $id && $auth->roleID != 2) 
        {
            return Response::json(['error' => 'You do not have permission.']);
        }

        $users = User::where("users.spaceID", $id)->select('users.id', 'users.name', 'users.roleID', 'users.title', 'users.email')->get();
        
        foreach($users as $uKey => $user)
        {
            $role = Role::find($user->roleID);
            $user->role = $role;

            $checks = Appearance::where('userID', $user->id)->get();
            $checks = count($checks);
            $user->checkins = $checks;
        }

        $roles = Role::where('id', '!=', 1)->get();
        
        return Response::json(['users' => $users, 'roles' => $roles]);
    }

    public function changeRole(Request $request)
    {
        $rules = [
            'userID' => 'required',
            'roleID' => 'required',
        ];

        $validator = Validator::make(Purifier::clean($request->all()), $rules);

        if ($validator->fails()) {
            return Response::json(['error' => 'Invalid form input.']);
        }

        $userID = $request->input('userID');
        $roleID = $request->input('roleID');

        $user = User::find($userID);
        $user->roleID = $roleID;
        $user->save();

        return Response::json(['success' => 'Role Updated.']);

    }
}
