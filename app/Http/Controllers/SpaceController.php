<?php

namespace App\Http\Controllers;

use App\Workspace;
use App\User;
use App\Event;
use App\Subscriptionplan;
use App\Appearance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Purifier;
use Hash;
use JWTAuth;
use DateTime;
use DateInterval;

class SpaceController extends Controller
{

    public function __construct()
    {
        $this->middleware('jwt.auth', ['only' => [
            'update'
        ]]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Response::json(Workspace::all());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

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
        $slug = (strtolower($name));
        $slug = str_replace(' ', '-', $slug);
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
        $unhash = $request->input('password');
        $password = $request->input('password');

        $check = User::where('email', $useremail)->first();

        if (!empty($check)) {
            return Response::json(['error' => 'Email already in use']);
        }

        $slugCheck = Workspace::where('slug', $slug)->first();

        if (!empty($slugCheck)) {
            $string = str_random(3);
            $slug = $slug . '-' . $string;
        }

        $coordinates = $this->getGeoLocation($address, $city, $state);
        $lon = $coordinates->results[0]->geometry->location->lng;
        $lat = $coordinates->results[0]->geometry->location->lat;

        // optional input
        // Check for valid image upload
        if (!empty($_FILES['logo'])) {
            // Check for file upload error
            if ($_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
                return Response::json(["error" => "Upload failed with error code " . $_FILES['logo']['error']]);
            }
            // checks for valid image upload
            $info = getimagesize($_FILES['logo']['tmp_name']);

            if ($info === false) {
                return Response::json(["error" => "Unable to determine image type of uploaded file"]);
            }

            // checks for valid image upload
            if (($info[2] !== IMAGETYPE_GIF)
                && ($info[2] !== IMAGETYPE_JPEG)
                && ($info[2] !== IMAGETYPE_PNG)) {
                return Response::json(["error" => "Not a gif/jpeg/png"]);
            }

            // Get profile image input
            $logo = $request->file('logo');
        }

        if (!empty($_FILES['logo'])) {
            // Check for file upload error
            if ($_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
                return Response::json(["error" => "Upload failed with error code " . $_FILES['logo']['error']]);
            }
            // checks for valid image upload
            $info = getimagesize($_FILES['logo']['tmp_name']);

            if ($info === false) {
                return Response::json(["error" => "Unable to determine image type of uploaded file"]);
            }

            // checks for valid image upload
            if (($info[2] !== IMAGETYPE_GIF)
                && ($info[2] !== IMAGETYPE_JPEG)
                && ($info[2] !== IMAGETYPE_PNG)) {
                return Response::json(["error" => "Not a gif/jpeg/png"]);
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
            $workspace->logo = $request->root() . '/storage/logo/' . $logoName;
        }

        $workspace->save();

        $spaceID = $workspace->id;

            // Check for valid image upload
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

            // checks for valid image upload
            if (($info[2] !== IMAGETYPE_GIF)
                && ($info[2] !== IMAGETYPE_JPEG)
                && ($info[2] !== IMAGETYPE_PNG)) {
                return Response::json(["error" => "Not a gif/jpeg/png"]);
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
            $avatar = $request->root() . '/storage/avatar/' . $avatarName;
        } else {
            $sub = substr($name, 0, 2);
            $avatar = "https://invatar0.appspot.com/svg/" . $sub . ".jpg?s=100";
        }

        $user->avatar = $avatar;
        $user->subscriber = 0;
        $user->save();

            // $url = 'http://challenges.innovationmesh.com/api/signUp';
            // $data = array('email' => $useremail, 'name' => $username, 'password' => $unhash );

            // $options = array(
                // 'http' => array(
                    // 'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    // 'method'  => 'POST',
                    // 'content' => http_build_query($data)
                // )
            // );

            // $context  = stream_context_create($options);
            // $result = file_get_contents($url, false, $context);

            // $url = 'http://lms.innovationmesh.com/signUp/';
            // $data = array('email' => $useremail, 'username' => $username, 'password' => $unhash );

            // $options = array(
                // 'http' => array(
                    // 'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    // 'method'  => 'POST',
                    // 'content' => http_build_query($data)
                // )
            // );

            // $context  = stream_context_create($options);
            // $result = file_get_contents($url, false, $context);

        return Response::json($workspace->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Workspace  $workspace
     * @return \Illuminate\Http\Response
     */
    public function show(Workspace $workspace)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Workspace  $workspace
     * @return \Illuminate\Http\Response
     */
    public function edit(Workspace $workspace)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Workspace  $workspace
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Workspace $workspace)
    {

        $auth = Auth::user();
        if ($auth->spaceID != @$workspace->id || $auth->roleID != 2) {
            return Response::json(['error' => 'You do not have permission.']);
        }

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


        // optional input
        // Check for valid image upload
        if (!empty($_FILES['logo'])) {
            // Check for file upload error
            if ($_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
                return Response::json(["error" => "Upload failed with error code " . $_FILES['logo']['error']]);
            }
            // checks for valid image upload
            $info = getimagesize($_FILES['logo']['tmp_name']);

            if ($info === false) {
                return Response::json(["error" => "Unable to determine image type of uploaded file"]);
            }

            // checks for valid image upload
            if (($info[2] !== IMAGETYPE_GIF)
                && ($info[2] !== IMAGETYPE_JPEG)
                && ($info[2] !== IMAGETYPE_PNG)) {
                return Response::json(["error" => "Not a gif/jpeg/png"]);
            }

            // Get profile image input
            $logo = $request->file('logo');
        }

        $workspace = Workspace::where('id', $spaceID)->orWhere('slug', $spaceID)->first();

        if (!empty($name)) $workspace->name = $name;
        if (!empty($city)) $workspace->city = $city;
        if (!empty($address)) $workspace->address = $address;
        if (!empty($state)) $workspace->state = $state;
        if (!empty($zipcode)) $workspace->zipcode = $zipcode;
        if (!empty($email)) $workspace->email = $email;
        if (!empty($website)) $workspace->website = $website;
        if (!empty($phone_number)) $workspace->phone_number = $phone_number;
        if (!empty($description)) $workspace->description = $description;
        if (!empty($facebook)) $workspace->facebook = $facebook;
        if (!empty($twitter)) $workspace->twitter = $twitter;
        if (!empty($instagram)) $workspace->instagram = $instagram;
        if (!empty($key)) $workspace->stripe = $key;
        if (!empty($logo)) {
            $logoName = $logo->getClientOriginalName();
            $logo->move('storage/logo/', $logoName);
            $workspace->logo = $request->root() . '/storage/logo/' . $logoName;
        }

        // persist workspace to database
        if (!$workspace->save()) return Response::json(['error' => 'Account not created']);
        else return Response::json(['success' => $workspace->name . ' updated!']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Workspace  $workspace
     * @return \Illuminate\Http\Response
     */
    public function destroy(Workspace $workspace)
    {
        //
    }


    private function getGeoLocation($address, $city, $state)
    {
        $address_array = explode(' ', $address);

        $length = count($address_array);

        $URIparam = '';
        for ($i = 0; $i < $length; $i++) {
            if ($i != ($length - 1))
                $URIparam .= $address_array[$i] . '+';
            else
                $URIparam .= $address_array[$i];
        }
        return json_decode(file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address=' . $URIparam . ',+' . $city . ',+' . $state . '&key=AIzaSyCrhrhhqlvkuQkAycbZzVS5f-ym_tpFs0o'));
    }
}