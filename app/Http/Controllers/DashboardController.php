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
use App\Services\Stripe\SubscriptionService;
// Eloquent Models
use App\User;
use App\Appearance;
use DateTime;

class DashBoardController extends Controller {
    protected $appearanceService;
    protected $joinsService;
    public function __construct(
        AppearanceService $appearanceService,
        JoinsService $joinsService
    ) {
        $this->middleware('jwt.auth', ['only' => [
            // 'getCustomerSignUps',
             'allBalancesFromDate',
            'getThisMonthsCustomers'
        ]]);
        $this->appearanceService = $appearanceService;
        $this->joinsService = $joinsService;
    }

    public function allBalancesFromDate($start, $end) {
        $user = Auth::user();
        $spaceID = $user->spaceID;
        $space = Workspace::find($spaceID)->makeVisible('stripe');
        $subscriptionService = new SubscriptionService($space->stripe);
        $balances = $subscriptionService->getBalancesFromDateRange($start, $end);
        if (array_key_exists('error', $balances)) {
            return Response::json(['error' => $balances['error']]);
        } else {
            return Response::json(['balances' => $balances]);
        }
    }
    
    public function getThisMonthsCustomers($start, $end) {
        $user = Auth::user();
        $spaceID = $user->spaceID;
        $space = Workspace::find($spaceID)->makeVisible('stripe');
        $subscriptionService = new SubscriptionService($space->stripe);
        $customers = $subscriptionService->getThisMonthsCustomers($start, $end);
        if ($customers == 0) {
            return Response::json(['error' => true]);
        } else {
            return Response::json(['customers'  => $customers]);
        }

    }

    /**
     * @param $spaceId
     * @return Illuminate\Support\Facades\Response::class
     */
    public function Joins($slug) {
        $space = Workspace::where('slug', $slug)->first();
        $spaceID = $space->id;
        $joins = $this->appearanceService->getAllJoins($spaceID);
        $unixTimeStamp = time();
        if (array_key_exists('error', $joins)) {
            return Response::json([
                'updatedAt' => $unixTimeStamp,
                'error' => $joins['error']
            ]);
        } else {
            return Response::json([
                'data' => $joins,
                'updatedAt' => $unixTimeStamp,
            ]);
        }
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
        $unixTimeStamp = time();
        if (array_key_exists('error', $appearances)) {
            return Response::json([
                'error' => $appearances['error'],
                'updatedAt' => $unixTimeStamp,
            ]);
        } else {
            $unixTimeStamp = time();
            return Response::json([
                'data' => $appearances,
                'updatedAt' => $unixTimeStamp,
            ]);
        }
    }

    public function Events($slug)
    {
        $space = Workspace::where('slug', $slug)->first();
        $spaceID = $space->id;
        $events = $this->appearanceService->getAllEvents($spaceID);
        $unixTimeStamp = time();
        if (array_key_exists('error', $events)) {
            return Response::json([
                'error' => $events['error'],
                'updatedAt' => $unixTimeStamp,
            ]);
        } else {
            return Response::json([
                'data' => $events,
                'updatedAt' => $unixTimeStamp,
            ]);
        }
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

    public function inviteHelper(){

    }

    public function log($message) {
        Log::error($message);
    }
    public function email() {

        Mail::send('emails.welcome', array('key' => 'value'), function ($message) {
            $message->to('austin.conder@outlook.com', 'Austin Conder')->subject('Welcome!');
            $message->from('laravel@example.com', 'Laravel');
        });
    }
}
