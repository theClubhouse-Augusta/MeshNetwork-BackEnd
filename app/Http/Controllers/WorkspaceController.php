<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Response;
use Purifier;
use Hash;
use Auth;
use JWTAuth;
use DateTime;
use DateInterval;
use Illuminate\Support\Facades\Validator;
use App\User;
use App\Workspace;
use App\Event;
use App\Subscriptionplan;
use App\Appearance;
use Carbon\Carbon;

class WorkspaceController extends Controller
{
    /** JWTAuth for Routes
     * @param void
     * @return void
     */
    public function __construct() {
        $this->middleware('jwt.auth', ['only' => [
        // 'store',
        // 'get',
        // 'show',
        // 'approve',
         'update',
        // 'events',
        // 'bookables'
        ]]);
    }

    public function store(Request $request) {
        $rules = [
            'name' => 'required|string',
            'city' => 'required|string',
            'address' => 'required|string',
            'state' => 'required|string',
            'zipcode' => 'required|string',
            'email' => 'required|string',
            'website' => 'required|string',
            'description' => 'required|string',
            'logo' => 'nullable|string',
            'username' => 'required|string',
            'password' => 'required|string',
            'useremail' => 'required|string',
        ];
        // Validate input against rules
        $validator = Validator::make(Purifier::clean($request->all()), $rules);

        if ($validator->fails()) {
            return Response::json(['error' => 'You must fill out all fields.']);
        }

        // test input

        // production input
        // Logged in user
        // $userID = Auth::id();
        // $user = User::find($userID);
        // $roleID = $user->roleID;
        // return $roleID;
        // if ($roleID != 1 && $roleID != 2) {
        //     return Response::json(['error' => 'invalid credentials']);
        // }

        // form input
        $name = $request->input('name');
        $slug = str_replace(' ', '-', $name);
        $slug = preg_replace('/[^A-Za-z0-9\-]/', '', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $city = $request->input('city');
        $address = $request->input('address');
        $state = $request->input('state');
        $zipcode = $request->input('zipcode');
        $email = $request->input('email');
        $website = $request->input('website');
        $phone_number = $request->input('phone_number');
        $description = $request->input('description');

        $username = $request->input('username');
        $useremail = $request->input('useremail');
        $password = $request->input('password');

        $check = User::where('email', $useremail)->first();

        if (!empty($check)) {
            return Response::json(['error' => 'Email already in use']);
        }

        $coordinates = $this->getGeoLocation($address, $city, $state);
        $lon = $coordinates->results[0]->geometry->location->lng;
        $lat = $coordinates->results[0]->geometry->location->lat;

        // optional input
        // Check for valid image upload
        if (!empty($_FILES['logo']))
        {
            // Check for file upload error
            if ($_FILES['logo']['error'] !== UPLOAD_ERR_OK)
            {
                return Response::json([ "error" => "Upload failed with error code " . $_FILES['logo']['error']]);
            }
            // checks for valid image upload
            $info = getimagesize($_FILES['logo']['tmp_name']);

            if ($info === FALSE)
            {
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
            $logo = $request->file('logo');
        }

        if (!empty($_FILES['logo']))
        {
            // Check for file upload error
            if ($_FILES['logo']['error'] !== UPLOAD_ERR_OK)
            {
                return Response::json([ "error" => "Upload failed with error code " . $_FILES['logo']['error']]);
            }
            // checks for valid image upload
            $info = getimagesize($_FILES['logo']['tmp_name']);

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
            $logo = $request->file('logo');
            }
            // Ensure unique email
            $check = Workspace::where('name', $name)->first();

            if (!empty($check)) {
                return Response::json(['error' => 'Name already in use']);
            }

            // create new App\Workspace;
            $workspace = new Workspace;
            $workspace->name = $name;
            $workspace->slug = $slug;
            $workspace->city = $city;
            $workspace->address = $address;
            $workspace->state = $state;
            $workspace->zipcode = $zipcode;
            $workspace->email = $email;
            $workspace->website = $website;
            $workspace->phone_number = $phone_number;
            $workspace->description = $description;
            $workspace->lon = $lon;
            $workspace->lat = $lat;
            $workspace->pub_key = 0;
            if (!empty($logo)) {
                $logoName = $logo->getClientOriginalName();
                $logo->move('storage/logo/', $logoName);
                $workspace->logo = $request->root().'/storage/logo/'.$logoName;
            }

            $workspace->save();

            $spaceID = $workspace->id;

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

            $user = new User;
            // Required input
            $user->name = $username;
            $user->email = $useremail;
            $user->spaceID = $spaceID;
            $user->roleID = 2;
            $user->password = Hash::make($password);

            // Profile Picture
            if (!empty($avatar)) {
              $avatarName = $avatar->getClientOriginalName();
              $avatar->move('storage/avatar/', $avatarName);
              $avatar = $request->root().'/storage/avatar/'.$avatarName;
            } else {
               $sub = substr($name, 0, 2);
               $avatar = "https://invatar0.appspot.com/svg/".$sub.".jpg?s=100";
            }

            $user->avatar = $avatar;
            $user->subscriber = 0;
            $user->save();

            return Response::json($workspace->id);
        }


    private function getGeoLocation($address, $city, $state) {
        $address_array = explode(' ', $address);

        $length = count($address_array);

        $URIparam = '';
        for ($i = 0; $i < $length; $i++) {
            if ( $i != ($length - 1) )
                $URIparam .= $address_array[$i].'+';
            else
                $URIparam .= $address_array[$i];
        }
        return json_decode(file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.$URIparam.',+'.$city.',+'.$state.'&key=AIzaSyCrhrhhqlvkuQkAycbZzVS5f-ym_tpFs0o'));

        }

    public function get() {
        return Response::json(Workspace::all());
    }

    public function show($slug)
    {
    // Ensure user has admin privalages
  //   $admin = Auth::user();
  //   $id = $admin->roleID;
  //   if ($id != 1 && $id != 2) {
  //     return Response::json(['error' => 'invalid credentials']);
  //   }
        $space = Workspace::where('slug', $slug)
                            ->orWhere('id', $slug)
                            ->first();
        if (empty($space))
        {
            return Response::json([ 'error' => 'No space with id: '.$spaceID ]);
        }
        return Response::json($space);
    }


    public function approve($spaceID, $status)
    {
        // // Ensure user has admin privalages
        // $admin = Auth::user();
        // $id = $admin->roleID;
        // if ($id != 1) {
        //   return Response::json(['error' => 'invalid credentials']);
        // }
        $workspace = Workspace::where('id', $spaceID)->orWhere('slug', $spaceID)->first();
        $workspace->status = $status;
        if (!$workspace->save())
        {
            return Response::json([ 'error' => 'Database error' ]);
        }
        return Response::json([ 'success' => 'Workspace status: '.$status ]);
    }

    public function update(Request $request)
    {
        // Ensure user has admin privalages
        // $org = Auth::user();
        // $id = $org->id;
        // if () {
        // return Response::json(['error' => 'invalid credentials']);
        // }
        $rules = [
            'spaceID' => 'required|string',
            'name' => 'nullable|string',
            'city' => 'nullable|string',
            'address' => 'nullable|string',
            'state' => 'nullable|string',
            'zipcode' => 'nullable|string',
            'email' => 'nullable|string',
            'website' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'description' => 'nullable|string',
            'logo' => 'nullable|string',
            'stripe' => 'nullable|string',
            'facebook' => 'nullable|string',
            'logo' => 'nullable|string',
            'twitter' => 'nullable|string',
            'instagram' => 'nullable|string',
            'key' => 'nullable|string',
        ];
        // Validate input against rules
        $validator = Validator::make(Purifier::clean($request->all()), $rules);

        if ($validator->fails()) {
            return Response::json(['error' => 'You must fill out all fields.']);
        }

        // form input
        $spaceID = $request->input('spaceID');
        $name = $request->input('name');
        $city = $request->input('city');
        $address = $request->input('address');
        $state = $request->input('state');
        $zipcode = $request->input('zipcode');
        $email = $request->input('email');
        $website = $request->input('website');
        $phone_number = $request->input('phone_number');
        $description = $request['description'];
        $facebook = $request->input('facebook');
        $twitter = $request->input('twitter');
        $instagram = $request->input('instagram');
        $key = $request->input('key');

        $auth = Auth::user();
        if($auth->spaceID != $spaceID || $auth->roleID != 2)
        {
          return Response::json(['error' => 'You do not have permission.']);
        }

        // optional input
        // Check for valid image upload
        if (!empty($_FILES['logo']))
        {
            // Check for file upload error
            if ($_FILES['logo']['error'] !== UPLOAD_ERR_OK)
            {
                return Response::json([ "error" => "Upload failed with error code " . $_FILES['logo']['error']]);
            }
            // checks for valid image upload
            $info = getimagesize($_FILES['logo']['tmp_name']);

            if ($info === FALSE)
            {
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
            $logo = $request->file('logo');
        }

        $workspace = Workspace::where('id', $spaceID)->orWhere('slug', $spaceID)->first();

        if(!empty($name)) $workspace->name = $name;
        if(!empty($city)) $workspace->city = $city;
        if(!empty($address)) $workspace->address = $address;
        if(!empty($state)) $workspace->state = $state;
        if(!empty($zipcode)) $workspace->zipcode = $zipcode;
        if(!empty($email)) $workspace->email = $email;
        if(!empty($website)) $workspace->website = $website;
        if(!empty($phone_number)) $workspace->phone_number = $phone_number;
        if(!empty($description)) $workspace->description = $description;
        if (!empty($facebook)) $workspace->facebook = $facebook;
        if (!empty($twitter)) $workspace->twitter = $twitter;
        if (!empty($instagram)) $workspace->instagram = $instagram;
        if (!empty($key)) $workspace->stripe = $key;
        if (!empty($logo)) {
            $logoName = $logo->getClientOriginalName();
            $logo->move('storage/logo/', $logoName);
            $workspace->logo = $request->root().'/storage/logo/'.$logoName;
        }

        // persist workspace to database
        if (!$workspace->save()) return Response::json(['error' => 'Account not created']);
        else return Response::json([ 'success' => $workspace->name.' updated!' ]);
    }

    public function events($spaceID)
    {
        // Ensure user has admin privalages
        // $admin = Auth::user();
        // $id = $admin->roleID;
        // if ($id != 1 && $id != 2) {
        //   return Response::json(['error' => 'invalid credentials']);
        // }

        // TODO provide check for dates or let

        $events = Event::where('events.spaceID', $spaceID)
        ->join('eventdates', 'events.id', '=', 'eventdates.eventID')
        ->select('events.id', 'events.spaceID', 'events.title', 'events.description', 'events.image', 'eventdates.start', 'eventdates.end')
        ->paginate(12);
        foreach($events as $key => $event) {
          $event->start = Carbon::createFromTimeStamp(strtotime($event->start))->diffForHumans();
          $event->end = Carbon::createFromTimeStamp(strtotime($event->end))->diffForHumans();
        }

        return Response::json($events);

    }


    public function getSubscriptions($spaceID) {
       $space = Workspace::where('id', $spaceID)->orWhere('slug', $spaceID)->select('stripe')->first();
        \Stripe\Stripe::setApiKey($space->stripe);
        $plans = \Stripe\Plan::all();
        return Response::json($plans);
    }

    public function getKey($spaceID) {
       $workspace = Workspace::where('id', $spaceID)->orWhere('slug', $spaceID)->first();
       return $workspace->pub_key;
    }

    public function spaceOrganizers($spaceID)
    {
      $users = User::where('spaceID', $spaceID)->where('roleID', 2)->get();

      return Response::json($users);
    }

    public function getSpaceStats($spaceID)
    {
      $members = User::where('spaceID', $spaceID)->get();
      $memberCount = count($members);

      $events = Event::where('spaceID', $spaceID)->get();
      $eventCount = count($events);

      $checkins = Appearance::where('spaceID', $spaceID)->get();
      $checkinCount = count($checkins);

      $now = new DateTime("now");
      $now->add(new DateInterval('P1D'));
      $lastMonth = new DateTime("now");
      $lastMonth->sub(new DateInterval('P32D'));

      $start = date('Y-m-d', $lastMonth->getTimeStamp());
      $end = date('Y-m-d', $now->getTimeStamp());

      $thisMonthCheckIns = count(Appearance::where('spaceID', $spaceID)
                                  ->whereBetween('created_at', [$start, $end])
                                  ->get());
      return Response::json([
          'memberCount' => $memberCount, 
          'eventCount' => $eventCount, 
          'checkinCount' => $checkinCount,
          'thisMonthCheckIns' => $thisMonthCheckIns,
     ]);
    }

}
