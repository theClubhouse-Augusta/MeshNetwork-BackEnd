<?php
namespace App\Services;

use Illuminate\Support\Facades\Validator;
use \Mews\Purifier\Facades\Purifier;

// Models
use App\User;
use App\Workspace;
use App\Company;

class InputValidator
{
  public function validateSignUp($request, $space_ID = null, $avatar = null)
  {
    $rules = [
      'name' => 'required|string',
      'password' => 'required|string',
      'recaptcha' => 'required|string',
      'email' => 'required|string',
      'spaceID' => 'nullable|string',
      'plan' => 'nullable|string',
      'customerToken' => 'nullable|string',
      'tags' => 'nullable|string',
      'avatar' => 'nullable|string',
    ];
    $emailAlreadyInUse = !empty(User::where('email', $request['email'])->first());
    if ($emailAlreadyInUse) {
      return [
        'isValid' => false,
        'message' => 'another user is currently enrolled with that email address',
      ];
    }

        // Validate input against rules
    $validator = Validator::make(Purifier::clean($request->all()), $rules);
    if ($avatar != null) {
      $this->imageFails = $this->invalidImageUpload($avatar);
      if ($this->imageFails) {
        return [
          'isValid' => false,
          'message' => 'invalid image upload',
        ];

      }
    }


    if ($validator->fails()) {
      return [
        'isValid' => false,
        'message' => 'you must fill out all fields'
      ];
    } else {
      return [
        'isValid' => true,
      ];
    }
  }

  public function validateCompanyStore($request, $logo = null)
  {
    $rules = [
      'name' => 'required|string',
      'logo' => 'nullable|string',
      'employeeCount' => 'nullable|string',
      'description' => 'nullable|string',
      'tags' => 'required|string',
      'url' => 'nullable|string',
    ];
    // Validate input against rules
    $validator = Validator::make(Purifier::clean($request->all()), $rules);
    $imageFails = ($logo != null) ? $this->invalidImageUpload($logo) : false;
    $nameAlreadyInUse = !empty(Company::where('name', $request['name'])->first());

    if ($validator->fails()) {
      return [
        'isValid' => false,
        'message' => 'you must fill out all fields'
      ];
    } else if ($imageFails) {
      return [
        'isValid' => false,
        'message' => 'invalid image upload',
      ];
    } else if ($nameAlreadyInUse) {
      return [
        'isValid' => false,
        'message' => 'Another collaborative workspace has taken that name',
      ];
    } else {
      return [
        'isValid' => true,
      ];
    }
  }

  public function validateSpaceStore($request, $logo = null)
  {
    $rules = [
      'name' => 'required|string',
      'city' => 'required|string',
      'address' => 'required|string',
      'recaptcha' => 'required|string',
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
    $imageFails = ($logo != null) ? $this->invalidImageUpload($logo) : false;
    $nameAlreadyInUse = !empty(Workspace::where('name', $request['name'])->first());

    if ($validator->fails()) {
      return [
        'isValid' => false,
        'message' => 'you must fill out all fields'
      ];
    } else if ($imageFails) {
      return [
        'isValid' => false,
        'message' => 'invalid image upload',
      ];
    } else if ($nameAlreadyInUse) {
      return [
        'isValid' => false,
        'message' => 'Another collaborative workspace has taken that name',
      ];
    } else {
      return [
        'isValid' => true,
      ];
    }
  }

  static public function validateUpdateCustomerMeshEmail($request)
  {
    $rules = [
      'email' => 'required|string',
      'customer_id' => 'required|string',
    ];
        // Validate input against rules
    $validator = Validator::make(Purifier::clean($request->all()), $rules);
    if ($validator->fails()) {
      return [
        'isValid' => false,
        'message' => $request['email'],
      ];
    } else {
      return [
        'isValid' => true,
      ];
    }
  }

  private function invalidImageUpload($image)
  {
    // Check for valid image upload
    if (!empty($image)) {
      // Check for file upload error
      if ($image['error'] !== UPLOAD_ERR_OK) {
        return true;
      }
      // checks for valid image upload
      try {
        $info = getimagesize($image['tmp_name']);
      } catch (\Exception $e) {
        return true;
      }

      if ($info === false) {
        return true;
      }

      // checks for valid image upload
      if (($info[2] !== IMAGETYPE_GIF)
        && ($info[2] !== IMAGETYPE_JPEG)
        && ($info[2] !== IMAGETYPE_PNG)) {
        return true;
      }
    }
    return false;
  }
}
