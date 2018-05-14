<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Mews\Purifier\Facades\Purifier;

use App\Company;
use App\Companyvertical;
use App\Companytovertical;
use App\Services\InputValidator;

class CompanyController extends Controller
{
  private $inputValidator;
  public function __construct(InputValidator $inputValidator)
  {
    $this->inputValidator = $inputValidator;
    $this->middleware('jwt.auth', ['only' => [
      'store',
      'update',
      'getCompanyOfLoggedInUser',
      'getCompany',
    ]]);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    DB::beginTransaction();

    $validInput = array_key_exists('logo', $_FILES)
      ? $this->inputValidator->validateCompanyStore($request, $_FILES['logo'])
      : $this->inputValidator->validateCompanyStore($request);

    if (!$validInput['isValid']) {
      return Response::json(['error' => $validInput['message']]);
    }
    $company = null;
    $update = json_decode($request['update']);
    if ($update) {
      $company = Company::find($request['companyId']);
      $company->fill(
        $request->except([
          'tags',
          'logo',
          'update',
          'companyId',
        ])
      );
    } else {
      $company = new Company(
        $request->except([
          'tags',
          'logo',
          'update',
          'companyId',
        ])
      );
    }
    $logo = $request->file('logo');
    if (!empty($logo)) {
      $logoName = $logo->getClientOriginalName();
      $logo->move('storage/company/logo/', $logoName);
      $company->logo = $request->root() . '/storage/company/logo/' . $logoName;
    }

    $success = $company->save();
    if (!$success) {
      DB::rollBack();
      return Response::json(['error' => 'Space not created.']);
    } else {
      $tags = json_decode($request['tags']);
      if (!empty($tags)) {
        foreach ($tags as $key => $tag) {
          if (!property_exists($tag, 'id')) {
            $check = Companyvertical::where('name', $tag->value)->first();
            if (empty($check)) {
              $vertical = new Companyvertical;
              $vertical->name = $tag->value;
              // Persist App\Skill to database
              $success = $vertical->save();
              if (!$success) {
                DB::rollBack();
                return Response::json(['error' => 'database error']);
              }
            }
          }
        }
      }

      if (!empty($tags)) {
        foreach ($tags as $key => $tag) {
          $verticalTag = Companyvertical::where('name', $tag->value)->first();
          $check = Companytovertical::where('companyID', $company->id)
            ->where('verticalID', $verticalTag->id)
            ->first();
          if (empty($check)) {
             // Create new EventSkill
            $companyVertical = new Companytovertical;
            $companyVertical->companyID = $company->id;
            $companyVertical->verticalID = $verticalTag->id;
             // Persist App\Skill to database
            $success = $companyVertical->save();
            if (!$success) {
              DB::rollBack();
              return Response::json(['error' => 'eventSkill database error']);
            }
          }
        }
      }
      if ($update) {
        $verticalIdArray = [];
        $ignore = [];
        if (!empty($tags)) {
          foreach ($tags as $key => $tag) {
            if (property_exists($tag, 'id')) {
              array_push($verticalIdArray, $tag->id);
            }
            if (property_exists($tag, 'className')) {
              $v = Companyvertical::where('name', $tag->value)->first();
              array_push($ignore, $v->id);
            }
          }
        }
        $foos = Companytovertical::where('companyID', $company->id)
          ->select('verticalID')
          ->get()
          ->toArray();
        if (!empty($verticalIdArray)) {
          if (!empty($foos)) {
            foreach ($foos as $foo) {
              if ((!in_array($foo['verticalID'], $verticalIdArray)) && (!in_array($foo['verticalID'], $ignore))) {
                $bar = Companytovertical::where('companyID', $company->id)
                  ->where('verticalID', $foo['verticalID'])
                  ->first();
                if (!empty($bar)) {
                  $bar->delete();
                }
              }
            }
          }
        }
      }
      DB::commit();
    }
    if ($update) {
      return Response::json(['success' => 'Company profile updated']);
    } else {
      return Response::json(['success' => 'Company profile created']);
    }
  }

  public function update($companyID)
  {
    DB::beginTransaction();
    $validInput = array_key_exists('logo', $_FILES)
      ? $this->inputValidator->validateCompanyStore($request, $_FILES['logo'])
      : $this->inputValidator->validateCompanyStore($request);

    if (!$validInput['isValid']) {
      return Response::json(['error' => $validInput['message']]);
    }

    $company = new Company($request->except(['tags', 'logo', ]));
    $company->slug = $slug;
    $company->lon = $lon;
    $company->lat = $lat;
    $company->pub_key = 0;

    if (!empty($logo)) {
      $logoName = $logo->getClientOriginalName();
      $logo->move('storage/company/logo/', $logoName);
      $company->logo = $request->root() . '/storage/company/logo/' . $logoName;
    }

    $success = $company->save();
    if (!$success) {
      DB::rollBack();
      return Response::json(['error' => 'Space not created.']);
    } else {
      DB::commit();
    }

    //$spaceID = $company->id;
    //$roleID = 2;
    return Response::json(['success' => 'Company profile created']);
  }

  public function getCompanyOfLoggedInUser()
  {
    $userID = Auth::id();
    $company = Company::where('userID', $userID)->first();
    $companyVerticals = Companytovertical::where('companyID', $company->id)->get();
    $verticalsArray = [];
    foreach ($companyVerticals as $companyVertical) {
      $vertical = Companyvertical::find($companyVertical->verticalID);
      array_push($verticalsArray, [
        'label' => $vertical->name,
        'value' => $vertical->name,
        'id' => $vertical->id
      ]);
    }
    if (!empty($company)) {
      return Response::json([
        'company' => $company,
        'verticals' => $verticalsArray,
      ]);
    } else {
      return Response::json(['userID' => $userID]);
    }
  }
  public function getCompany($companyID)
  {
    $userID = Auth::id();
    $company = Company::find($companyID);
    $companyVerticals = Companytovertical::where('companyID', $companyID)->get();
    $verticalsArray = [];
    foreach ($companyVerticals as $companyVertical) {
      $vertical = Companyvertical::find($companyVertical->verticalID);
      array_push($verticalsArray, [
        'label' => $vertical->name,
        'value' => $vertical->name,
        'id' => $vertical->id
      ]);
    }
    if (!empty($company)) {
      return Response::json([
        'company' => $company,
        'verticals' => $verticalsArray,
      ]);
    } else {
      return Response::json(['error' => 'no company found']);
    }
  }

  public function allVerticals()
  {
    $verticals = Companyvertical::all();
    $verticalsArray = [];
    foreach ($verticals as $vertical) {
      array_push($verticalsArray, [
        'label' => $vertical->name,
        'value' => $vertical->name,
        'id' => $vertical->id
      ]);
    }
    return Response::json($verticalsArray);
  }
}
