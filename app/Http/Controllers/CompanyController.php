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

    $company = new Company($request->except([ 'tags', 'logo', ]));

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

    return Response::json(['success' => 'Company profile created']);
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

    $company = new Company($request->except([ 'tags', 'logo', ]));
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
    $company = Company::where('userID', Auth::id())->first();
    if (!empty($company)) {
      return Response::json(['company' => $company]);
    } 
  }
  public function getCompany($companyID)
  {
    $company = Company::find($companyID);
    if (!empty($company)) {
      return Response::json(['company' => $company]);
    } else {
      return Response::json(['error' => 'no company found']);
    }
  }
}
