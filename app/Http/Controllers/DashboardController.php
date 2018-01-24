<?php
namespace App\Http\Controllers;

use App\Workspace;
use Response;
use Auth;
use JWTAuth;
use Mail;
use Illuminate\Support\Facades\Log;

// Service Classes 
use App\Services\AppearanceService;
use App\Services\JoinsService;
use App\Services\RMarkdownService;

// Eloquent Models
use App\User;
use App\Appearance;

class DashBoardController extends Controller 
{
    protected $appearanceService;
    protected $joinsService;
    protected $rmarkdownService;
    
    public function __construct(
        AppearanceService $appearanceService, 
        JoinsService $joinsService,
        RMarkdownService $rmarkdownService 
    ) 
    {
        $this->appearanceService = $appearanceService;
        $this->joinsService = $joinsService;
        $this->rmarkdownService = $rmarkdownService;
    }

    /**
     * Generate Member Sign up data visualizations using RMarkdown 
     * @param $spaceId
     * @return Illuminate\Support\Facades\Response::class
     */
    public function Joins($slug) {
        $space = Workspace::where('slug', $slug)->first();
        $spaceID = $space->id;
        $joins = $this->appearanceService->getAllJoins($spaceID);
        return Response::json($joins);
    }

    /**
     * Generate Appearances visualizations using RMarkdown 
     * @param $spaceId
     * @return Illuminate\Support\Facades\Response::class
     */
    public function Appearances($slug) {
        $space = Workspace::where('slug', $slug)->first();
        $spaceID = $space->id;
        $appearances = $this->appearanceService->getAllAppearances($spaceID);
//        return Response::json($appearances);
        return Response::json([]);
    }

    public function appearanceForMonthYear($slug, $startMonth, $startYear, $endMonth, $endYear) {
        $space = Workspace::where('slug', $slug)->first();
        $spaceID = $space->id;
        $appearances =  $this->appearanceService->getAppearancesForMonthYear($spaceID, $startMonth, $startYear, $endMonth, $endYear);
        return Response::json($appearances);

    }

    public function inviteHelper() 
    {

    }

    public function log($message) {
        Log::error($message);
    }
    public function email() {

    Mail::send('emails.welcome', array('key' => 'value'), function($message) {
        $message->to('austin.conder@outlook.com', 'Austin Conder')->subject('Welcome!');
        $message->from('laravel@example.com', 'Laravel');
        });
    }
}