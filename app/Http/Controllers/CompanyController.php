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
      'foo',
    ]]);
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
