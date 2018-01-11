<?php
namespace App\Http\Controllers;

use Response;
use Auth;
use JWTAuth;

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
    public function Appearances($spaceId) {

        // Write head of RMarkdown File
//        $this->rmarkdownService->generateTitle("Appearances!");

        // Get appearances from database by occasion
          $appearances =  $this->appearanceService->getAllAppearances($spaceId);
          return Response::json($appearances);
//            'event' => $this->appearanceService->getEventAppearances($spaceId),
//            'work' => $this->appearanceService->getNonEventAppearances($spaceId, 'work'),
//            'booking' => $this->appearanceService->getNonEventAppearances($spaceId, 'booking'),
//            'student' => $this->appearanceService->getNonEventAppearances($spaceId, 'student'),
//            'invite' => $this->appearanceService->getNonEventAppearances($spaceId, 'invite')
//        );

        // Create a seperate dataset for each occasion
//        foreach ($appearances as $key => $appearance)
//        {
            // Insert data into RMarkdown Script
//            $this->rmarkdownService->generateMemberAppearancesRmd(
//                $appearance['firstYear'],
//                $appearance['lastYear'],
//                $appearance['firstMonth'],
//                $appearance['lastMonth'],
//                $appearance['memberAppearancesData'],
//                $key,
//                (count($appearances) - 1) // for keeping track of function calls
//            );
//        }
        // Generate individual tabs for each dataset
        // $this->rmarkdownService->generateTabs();

    }

    public function appearanceForMonthYear($spaceID, $startMonth, $startYear, $endMonth, $endYear
    ) {
        $appearances =  $this->appearanceService
                             ->getAppearancesForMonthYear($spaceID, $startMonth, $startYear, $endMonth, $endYear);
        return Response::json($appearances);
    }

    public function inviteHelper() 
    {

    }
}
