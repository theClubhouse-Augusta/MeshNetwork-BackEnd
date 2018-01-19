<?php
namespace App\Services;
use DateTime;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;

use App\Appearance;
use App\Workspace;

class AppearanceService {

    public function getAllAppearances($spaceId) {
        $space = Workspace::where('id', $spaceId)->orWhere('slug', $spaceId)->first();
        $spaceId = $space->id;
        $sortedAppearances = Appearance::where('spaceID', $spaceId)->orderBy('created_at', 'ASC')->get();
        $appearanceCount = count($sortedAppearances);

        if ( $appearanceCount == 0 )
            return Response::json(['error' => 'No appearance data available']);

        $firstAppearance = $sortedAppearances[0]->created_at;
        $firstYear = $firstAppearance->year;

        $lastAppearance = $sortedAppearances[( $appearanceCount - 1 )]->created_at;
        $lastYear = $lastAppearance->year;

        $appearances = array();
        for ($year = $firstYear; $year <= $lastYear; $year++) {
            for ($month = 1; $month <= 12; $month++) {
                $appearancesForMonth = count(Appearance::
                                       where('spaceID', $spaceId)
                                       ->whereYear('created_at', ( $year ) )
                                       ->whereMonth('created_at', ( $month ) )
                                       ->get()
                                 );
                if ( !empty($appearancesForMonth) ) {
                    if (array_key_exists("$month-$year", $appearances))
                        $appearances["$month-$year"] += $appearancesForMonth;
                    else
                        $appearances["$month-$year"] = $appearancesForMonth;

                }

            }
        }
        $appearancesArray = [];
        foreach ($appearances as $key => $appearance) {
            array_push($appearancesArray, [
                'name' => $key,
                'appearances' => $appearance,
            ]);
        }
        return $appearancesArray;
    }

    public function getAppearancesForMonthYear($spaceID, $startMonth, $startYear, $endMonth, $endYear) {
        $space = Workspace::where('id', $spaceId)->orWhere('slug', $spaceId)->first();
        $spaceId = $space->id;
        $start = date('Y-m-d', mktime(0, 0, 0, $startMonth, 1, $startYear));
        $end = date('Y-m-d', mktime(0, 0, 0, ($endMonth + 1), 1, $endYear));

        $sortedAppearances = Appearance::where('spaceID', $spaceID)
                                  ->whereBetween('created_at', [$start, $end])
                                  ->orderBy('created_at', 'ASC')
                                  ->get();

        if (count($sortedAppearances) == 0)
            return Response::json(['error' => 'No appearances in this date range']);

        $appearances = [];
        foreach ($sortedAppearances as $appearance) {
            $created_at = $appearance->created_at;
            $year = $created_at->year;
            $month = $created_at->month;

            if (array_key_exists("$month-$year", $appearances))
                $appearances["$month-$year"] += 1;
            else
                $appearances["$month-$year"] = 1;
        }

        $appearancesArray = [];
        foreach ($appearances as $key => $appearance) {
            array_push($appearancesArray, [
                'name' => $key,
                'appearances' => $appearance,
            ]);
        }
        return $appearancesArray;
    }

    /**
     * @param $spaceId
     * @return events@spaceID
     */
    public function getEventAppearances($spaceId) {
        // event
        $space = Workspace::where('id', $spaceId)->orWhere('slug', $spaceId)->first();
        $spaceId = $space->id;
        $sortedAppearances = Appearance::where('spaceID', $spaceId)
                                        ->where('occasion', 'Event' )
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

            $appearances = array();
            for ($year = 0; $year <= $yearSpan; $year++) {
                for ($month = 1; $month <= 12; $month++) {
                    $appearancesForMonth = count(Appearance::
                                           where('spaceID', $spaceId)
                                           ->where('occasion', 'Event')
                                           ->whereYear('created_at', ( $firstYear + $year ) )
                                           ->whereMonth('created_at', ( $month ) )
                                           ->get()
                                     );
                    if ( !empty($appearancesForMonth) ) {
                        array_push($appearances, $appearancesForMonth);
                    }
                }
            }
            return (
                array (
                    'memberAppearancesData' => $appearances,
                    'firstYear' => $firstYear,
                    'lastYear' => $lastYear,
                    'firstMonth' => $firstMonth,
                    'lastMonth' => $lastMonth
                )
            );

        }

    }

    public function getNonEventAppearances($spaceId, $occasion) {
        $space = Workspace::where('id', $spaceId)->orWhere('slug', $spaceId)->first();
        $spaceId = $space->id;
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

            $appearances = array();
            for ($year = 0; $year <= $yearSpan; $year++) {
                for ($month = 1; $month <= 12; $month++) {
                    $appearancesForMonth = count(Appearance::
                                          where('spaceID', $spaceId)
                                          ->where('occasion', $occasion)
                                          ->whereYear('created_at', ( $firstYear + $year ) )
                                          ->whereMonth('created_at', ( $month ) )
                                          ->get()
                                     );
                    if ( !empty($appearancesForMonth) ) {
                        array_push($appearances, $appearancesForMonth);
                    }
                }
            }
            return (
                array (
                    'memberAppearancesData' => $appearances,
                    'firstYear' => $firstYear,
                    'lastYear' => $lastYear,
                    'firstMonth' => $firstMonth,
                    'lastMonth' => $lastMonth
                )
            );
        }
    }
    private static function getByMonthYear($spaceID, $month, $year) {
        $space = Workspace::where('id', $spaceId)->orWhere('slug', $spaceId)->first();
        $spaceId = $space->id;
        $appearances = Appearance::where('spaceID', $spaceID)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->get();
            return $appearances;
    }
}
