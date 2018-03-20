<?php
namespace App\Http\Controllers;

use App\Workspace;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

// Service Classes
use App\Services\AppearanceService;
use App\Services\JoinsService;

// Eloquent Models
use App\User;
use App\Appearance;

class DashBoardController extends Controller {
    protected $appearanceService;
    protected $joinsService;
    public function __construct(
        AppearanceService $appearanceService,
        JoinsService $joinsService
    ) {
        $this->middleware('jwt.auth', ['only' => [
            'getCustomerSignUps'
        ]]);
        $this->appearanceService = $appearanceService;
        $this->joinsService = $joinsService;
    }

    /**
     * @param $spaceId
     * @return Illuminate\Support\Facades\Response::class
     */
    public function Joins($slug)
    {
        $space = Workspace::where('slug', $slug)->first();
        $spaceID = $space->id;
        $joins = $this->appearanceService->getAllJoins($spaceID);
        return Response::json($joins);
    }

    /**
     * @param $spaceId
     * @return Illuminate\Support\Facades\Response::class
     */
    public function Appearances($slug)
    {
        $space = Workspace::where('slug', $slug)->first();
        $spaceID = $space->id;
        $appearances = $this->appearanceService->getAllAppearances($spaceID);
        return Response::json($appearances);
    }

    public function appearanceForMonthYear($slug, $startMonth, $startYear, $endMonth, $endYear) {
        $space = Workspace::where('slug', $slug)->first();
        $spaceID = $space->id;
        $appearances = $this->appearanceService->getAppearancesForMonthYear($spaceID, $startMonth, $startYear, $endMonth, $endYear);
        return Response::json($appearances);
    }
    
    public function getUserCheckins($slug, $month, $year, $day, $endMonth, $endYear, $endDay) {
        $space = Workspace::where('slug', $slug)->first();
        $spaceID = $space->id;
        $users = $this->appearanceService->getUserCheckins($spaceID, $month, $year, $day, $endMonth, $endYear, $endDay);
        return Response::json($users);
    }
    
    public function getUserSignUps($slug, $month, $year, $day, $endMonth, $endYear, $endDay)  {
        $space = Workspace::where('slug', $slug)->first();
        $spaceID = $space->id;
        $users = $this->appearanceService->getUserSignUps($spaceID, $month, $year, $day, $endMonth, $endYear, $endDay);
        return Response::json($users);
    }

    public function getCustomerSignUps($slug, $month, $year, $day, $endMonth, $endYear, $endDay)  {
        $space = Workspace::where('slug', $slug)->first();
        $spaceID = $space->id;
        $users = $this->appearanceService->getCustomerSignUps($spaceID, $month, $year, $day, $endMonth, $endYear, $endDay);
        return Response::json($users);
    }

    public function inviteHelper()
    {

    }

    public function log($message)
    {
        Log::error($message);
    }
    public function email()
    {

        Mail::send('emails.welcome', array('key' => 'value'), function ($message) {
            $message->to('austin.conder@outlook.com', 'Austin Conder')->subject('Welcome!');
            $message->from('laravel@example.com', 'Laravel');
        });
    }
}
