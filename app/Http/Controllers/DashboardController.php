<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Response;
use Purifier;
use Hash;
use Auth;
use JWTAuth;
use Carbon\Carbon;

use App\User;
use App\Appearance;

class DashBoardController extends Controller {

    /** JWTAuth for Routes
     * @param void
     * @return void 
     */
    public function __construct() {
        $this->middleware('jwt.auth', ['only' => [
            // 'allUserJoins',
            // 'spaceAppearances',
        ]]);
    }

    private function getAllAppearances($spaceId) {

        $sortedAppearances = Appearance::
                            where('spaceID', $spaceId)
                            ->orderBy('created_at', 'ASC')
                            ->get();
        $appearanceCount = count($sortedAppearances);

        if ( !empty($appearanceCount) ) {
            $firstAppearance = $sortedAppearances[0]->created_at;
            $firstYear = $firstAppearance->year;
            $firstMonth = $firstAppearance->month;

            $lastAppearance = $sortedAppearances[( $appearanceCount - 1 )]->created_at;
            $lastYear = $lastAppearance->year;
            $lastMonth = $lastAppearance->month;

            $yearSpan = (int)$lastYear - (int)$firstYear;

            $res = array();
            for ($year = 0; $year <= $yearSpan; $year++) {
                for ($month = 1; $month <= 12; $month++) {
                    $joinsForMonth = count(
                                        Appearance::
                                        where('spaceID', $spaceId)
                                        ->whereYear('created_at', ( $firstYear + $year ) )
                                        ->whereMonth('created_at', ( $month ) )
                                        ->get()
                                    ); 
                    if ( !empty($joinsForMonth) ) array_push($res, $joinsForMonth);
                }        
            }
            return $res;
        }
    }

    /**
     * @param $spaceId
     * @return events@spaceID
     */
    private function getEventAppearances($spaceId) {
        // event
        $sortedAppearances = Appearance::
                            where('spaceID', $spaceId)
                            ->where('eventID', '!=', NULL )
                            ->orderBy('created_at', 'ASC')
                            ->get();

        $appearanceCount = count($sortedAppearances);

        if ( !empty($appearanceCount) ) {
            $firstAppearance = $sortedAppearances[0]->created_at;
            $firstYear = $firstAppearance->year;
            $firstMonth = $firstAppearance->month;

            $lastAppearance = $sortedAppearances[( $appearanceCount - 1 )]->created_at;
            $lastYear = $lastAppearance->year;
            $lastMonth = $lastAppearance->month;

            $yearSpan = (int)$lastYear - (int)$firstYear;

            $res = array();
            for ($year = 0; $year <= $yearSpan; $year++) {
                for ($month = 1; $month <= 12; $month++) {
                    $joinsForMonth = count(
                                        Appearance::
                                        where('spaceID', $spaceId)
                                        ->where('eventID', '!=', NULL)
                                        ->whereYear('created_at', ( $firstYear + $year ) )
                                        ->whereMonth('created_at', ( $month ) )
                                        ->get()
                                    ); 
                    if ( !empty($joinsForMonth) ) array_push($res, $joinsForMonth);
                }        
            }
            return $res;
        }
    }

    private function getNonEventAppearances($spaceId, $occasion) {

        $sortedAppearances = Appearance::where('spaceID', $spaceId)
                                        ->where('occasion', $occasion )
                                        ->orderBy('created_at', 'ASC')
                                        ->get();

        $appearanceCount = count($sortedAppearances);

        if ( !empty($appearanceCount) ) {
            $firstAppearance = $sortedAppearances[0]->created_at;
            $firstYear = $firstAppearance->year;
            $firstMonth = $firstAppearance->month;

            $lastAppearance = $sortedAppearances[( $appearanceCount - 1 )]->created_at;
            $lastYear = $lastAppearance->year;
            $lastMonth = $lastAppearance->month;

            $yearSpan = (int)$lastYear - (int)$firstYear;

            $res = array();
            for ($year = 0; $year <= $yearSpan; $year++) {
                for ($month = 1; $month <= 12; $month++) {
                    $joinsForMonth = count(
                                        Appearance::
                                        where('spaceID', $spaceId)
                                        ->where('occasion', $occasion)
                                        ->whereYear('created_at', ( $firstYear + $year ) )
                                        ->whereMonth('created_at', ( $month ) )
                                        ->get()
                                    ); 
                    if ( !empty($joinsForMonth) ) array_push($res, $joinsForMonth);
                }        
            }
            return $res;
        }
    }

    /**
     * Get all member signUps
     * @param void
     * @return Illuminate\Support\Facades\Response::class
     */
    public function allUserJoins() {
        // $user = User::find( Auth::id() )->spaceID;

        $sortedUsers = User::all()->sortBy('created_at');
        $memberCount = count($sortedUsers);

        $firstUser = $sortedUsers[0]->created_at;
        $firstYear = $firstUser->year;
        $firstMonth = $firstUser->month;

        $lastUser = $sortedUsers[( $memberCount - 1 )]->created_at;
        $lastYear = $lastUser->year;
        $lastMonth = $lastUser->month;

        $yearSpan = (int)$lastYear - (int)$firstYear;

        $res = array();
        for ($year = 0; $year <= $yearSpan; $year++) {
            for ($month = 1; $month <= 12; $month++) {
                $joinsForMonth = count(
                                    User::
                                    whereYear('created_at', ( $firstYear + $year ) )
                                    ->whereMonth('created_at', ( $month ) )
                                    ->get()
                                ); 
                if ( !empty($joinsForMonth) ) array_push($res, $joinsForMonth);
            }        
        }
        return Response::json($res);
    }


    /**
     * Get all appearances 
     * @param $spaceId
     * @return Illuminate\Support\Facades\Response::class
     */
    public function Appearances($spaceId) {
        $appearances = array(
            'all' => $this->getAllAppearances($spaceId), 
            'event' => $this->getEventAppearances($spaceId),
            'work' => $this->getNonEventAppearances($spaceId, 'work'),
            'booking' => $this->getNonEventAppearances($spaceId, 'booking'),
            'student' => $this->getNonEventAppearances($spaceId, 'student'),
            'invite' => $this->getNonEventAppearances($spaceId, 'invite')
        );
        return Response::json($appearances);
    }

    // 
    public function write() {
        $documentRoot = $_SERVER['DOCUMENT_ROOT'];
        $fp = fopen("$documentRoot/test/foo.Rmd", 'ab');
        $outputString = "one"."\t"."two\nthree";
        fwrite($fp, $outputString, strlen($outputString) );
    }

    public function inviteHelper() {

    }
}
