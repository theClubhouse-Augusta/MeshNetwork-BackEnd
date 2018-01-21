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
    public function Joins($spaceId, $year) 
    {
        $dataAndDates = $this->joinsService->spaceUserJoins($spaceId, $year);
        return Response::json($dataAndDates);
        // insert data and dates into R Markdown File
        $this->rmarkdownService->generateMemberJoinsRmd(
            $dataAndDates['firstYear'],
            $dataAndDates['lastYear'],
            $dataAndDates['firstMonth'], 
            $dataAndDates['lastMonth'],
            $dataAndDates['memberSignUpData']
        );
    }

    /**
     * Generate Appearances visualizations using RMarkdown 
     * @param $spaceId
     * @return Illuminate\Support\Facades\Response::class
     */
    public function Appearances($slug) {

//        $spaceID = Workspace::where('slug', $slug)->select('id')->first();
        $appearances =  $this->appearanceService->getAllAppearances($slug);
        return Response::json($appearances);
    }

    public function appearanceForMonthYear($slug, $startMonth, $startYear, $endMonth, $endYear) {
        $spaceID = Workspace::where('slug', $slug)->select('id')->first();
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