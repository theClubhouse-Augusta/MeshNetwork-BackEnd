<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Response;
use Purifier;
use JWTAuth;
use Auth;

use App\User;
use App\Role;

class RoleController extends Controller {
  public function __construct()   {
    $this->middleware('jwt.auth', ['only' => [
      'store',
      'get',
      'show',
      'delete'
    ]]);
  }

  /** 
   *  Store new App\Role
   * @param Illuminate\Support\Facades\Request::class
   * @return  Illuminate\Support\Facades\Response::class
  **/
  public function store(Request $request) {
    // required input
    $rules = [
      'name' => 'required',
    ];
    // Validate and purify input 
    $validator = Validator::make(Purifier::clean($request->all()), $rules);

    if ($validator->fails()) {
      return Response::json(['error' => 'Please fill out all fields.']);
    }

    // Ensure user has admin permissions
    $admin = Auth::user();
    $id = $admin->roleID;

    if ($id != 1) {
      return Response::json(['error' => 'invalid credentials' ]);
    }

    // get input
    $name = $request->input('name');
    // ensure unique name
    $check = Role::where('name', $name)->first();
    if (!empty($check)) {
      return Response::json([ 'error' => 'Role: '.$name.' already in use.' ]);
    }
    // create new role
    $role = new Role;
    $role->name = $name;

    if (!$role->save()) {
      return Response::json(['error' => 'database error' ]);
    }
    return Response::json([ 'success' => 'Created new role: '.$name ]);
  }

  
  /** 
   *  get all Roles
   * @param void 
   * @return  Illuminate\Support\Facades\Response::class
  **/
  public function get() {
    // ensure user has admin permissions
    $admin = auth::user();
    $id = $admin->roleID;

    if ($id != 1) {
      return response::json(['error' => 'invalid credentials' ]);
    }

    $roles = Role::all();
    return Response::json([ 'success' => $roles ]);
  }


  /** 
   *  Get all users with roleID [ in spaceID(s)]
   * @param Illuminate\Support\Facades\Request::class
   * @return  Illuminate\Support\Facades\Response::class
  **/
  public function show(Request $request) {
    // required input
    $rules = [
      'roleID' => 'required|string',
      'spaceID' => 'nullable|string',
    ];
    // Validate and purify input 
    $validator = Validator::make(Purifier::clean($request->all()), $rules);

    if ($validator->fails()) {
      return Response::json(['error' => 'Please fill out all fields.']);
    }
    // ensure user has admin permissions
    $admin = auth::user();
    $adminID = $admin->roleID;

    if ($adminID != 1) {
      // return response::json(['error' => 'invalid credentials' ]);
    }
    $roleID = $request->input('roleID');
    $spaceID = $request->input('spaceID');

    // return all users with roleID
    if (empty($spaceID)) {
      $users = User::where('roleID', $roleID)->get();
      if (count($users) != 0)  {
        return Response::json([ 'success' => $users ]);
      } else {
        return Response::json([ 'error' => 'No users with roleID: '.$roleID ]);
      }
    }

    // return only users in spaceID(s) with roleID
    $spaceIDs = explode(',', $spaceID);
    $res = array(); 
    foreach($spaceIDs as $SpaceID) {
      $users = User::where('spaceID', $SpaceID)->where('roleID', $roleID)->get();
      if (count($users) != 0) {
        array_push($res, $users);
      }
    }
    // if no users in db
    if (empty($res)) {
      return Response::json([ 'error' => 'No users in selected workspace with roleID: '.$roleID ]);
    }
    return Response::json([ 'success' => $res ]);
  }


  /** 
   *  Get all users with roleID [ in spaceID(s)]
   * @param App\Role->id 
   * @return  Illuminate\Support\Facades\Response::class
  **/
  public function delete($id) {
    // ensure user has admin permissions
    $admin = auth::user();
    $adminID = $admin->roleID;
    if ($adminID != 1) {
      return response::json(['error' => 'invalid credentials' ]);
    }
    $role = Role::where('id', $id)->first();
    if (empty($role)) {
      return response::json(['error' => 'Role id:'.$id.' does not exist in the database' ]);
    }

    if (!$role->delete()) {
      return response::json(['error' => 'Database error' ]);
    }
    return response::json(['success' => 'Role deleted successfully' ]);
  }
}
