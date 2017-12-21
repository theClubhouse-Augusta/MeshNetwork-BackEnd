<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Kiosk;

class KioskController extends Controller
{

    public function __construct() {
        $this->middleware('jwt.auth', [ 'only' => [ 'update' ]]);
    }

    public function update(Request $request) {
        $user = Auth::user();
        $spaceID = $user->$spaceID;
        
        $rules = [
          'inputPlaceholder' => 'nullable|string',
          'logo' => 'nullable|string',
          'primaryColor' => 'nullable|string',
          'secondaryColor' => 'nullable|string',
          'userWelcome' => 'nullable|string',
          'userThanks' => 'nullable|string',
        ];
        
        // Validate input against rules
        $validator = Validator::make(Purifier::clean($request->all()), $rules);

        if ( $validator->fails() ) {
            return Response::json(['error' => 'Invalid form input.']);
        }

        $inputPlaceholder = $request->input('inputPlaceholder');
        $primaryColor = $request->input('primaryColor');
        $secondaryColor = $request->input('secondaryColor');
        $userWelcome = $request->input('userWelcome');
        $userThanks = $request->input('userThanks');

        // Check for valid image upload
        if (!empty($_FILES['logo'])) {
        // Check for file upload error
        if ($_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
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
            $logo = $request->file('avatar');
        }
        $kiosk = new App\Kiosk;

        if (!empty($avatar)) {
            $avatarName = $avatar->getClientOriginalName();
            $avatar->move('storage/avatar/', $avatarName);
            $user->avatar = $request->root().'/storage/avatar/'.$avatarName;
        }

        if (!empty($inputPlaceholder)) $kiosk->inputPlaceholder = $inputPlaceholder;
        if (!empty($primaryColor)) $kiosk->primaryColor = $primaryColor;
        if (!empty($secondaryColor)) $kiosk->secondaryColor = $secondaryColor;
        if (!empty($userWelcome)) $kiosk->userWelcome = $userWelcome;
        if (!empty($userThanks)) $kiosk->userThanks = $userThanks;
        $sucess = $kiosk->save();
        return Response::json($success);
    }

    public function get($spaceID) {
        $kiosk = Kiosk::findOrFail($spaceID);
        return $kiosk;
    }
}
