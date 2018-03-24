<?php
namespace App\Services;
use DateTime;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;

use App\Appearance;
use App\User;
use App\Workspace;
use App\Event;
use App\Services\Stripe\SubscriptionService;

class AppearanceService {

    public function getAllAppearances($spaceID) {
        $sortedAppearances = Appearance::where('spaceID', $spaceID)
                ->orderBy('created_at', 'ASC')
                ->get();
        $appearanceCount = count($sortedAppearances);

        if ( $appearanceCount == 0 )
            return ['error' => 'No appearance data available'];

        $firstAppearance = $sortedAppearances[0]->created_at;
        $firstYear = $firstAppearance->year;

        $lastAppearance = $sortedAppearances[( $appearanceCount - 1 )]->created_at;
        $lastYear = $lastAppearance->year;

        $appearances = array();
        for ($year = $firstYear; $year <= $lastYear; $year++) {
            for ($month = 1; $month <= 12; $month++) {
                $appearancesForMonth = count(Appearance::
                        where('spaceID', $spaceID)
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

        $labels = [];
        $series = [];

        foreach ($appearances as $key => $appearance) {
            array_push($labels, $key);
            array_push($series, $appearance);
        }
        return [
            'labels' => $labels,
            'series' => [$series],
        ];
    }

    public function getAllJoins($spaceID) {
        $sortedJoins = User::where('spaceID', $spaceID)
                ->orderBy('created_at', 'ASC')
                ->get();
        $joinsCount = count($sortedJoins);

        if ( $joinsCount == 0 )
            return ['error' => 'No data available'];

        $firstJoin = $sortedJoins[0]->created_at;
        $firstYear = $firstJoin->year;

        $lastJoin = $sortedJoins[( $joinsCount - 1 )]->created_at;
        $lastYear = $lastJoin->year;

        $joins = array();
        for ($year = $firstYear; $year <= $lastYear; $year++) {
            for ($month = 1; $month <= 12; $month++) {
                $joinsForMonth = count(User::
                where('spaceID', $spaceID)
                    ->whereYear('created_at', ( $year ) )
                    ->whereMonth('created_at', ( $month ) )
                    ->get()
                );
                if ( !empty($joinsForMonth) ) {
                    if (array_key_exists("$month-$year", $joins))
                        $joins["$month-$year"] += $joinsForMonth;
                    else
                        $joins["$month-$year"] = $joinsForMonth;

                }

            }
        }
        $labels = [];
        $series = [];

        foreach ($joins as $key => $join) {
            array_push($labels, $key);
            array_push($series, $join);
        }
        return [
            'labels' => $labels,
            'series' => [$series],
        ];
    }

    public function getAllEvents($spaceID) {
        $sortedEvents = Event::where('spaceID', $spaceID)
                ->orderBy('created_at', 'ASC')
                ->get();
        $eventsCount = count($sortedEvents);

        if ( $eventsCount == 0 )
            return ['error' => 'No event data available'];

        $firstEvent = $sortedEvents[0]->created_at;
        $firstYear = $firstEvent->year;

        $lastEvent = $sortedEvents[( $eventsCount - 1 )]->created_at;
        $lastYear = $lastEvent->year;

        $events = array();
        for ($year = $firstYear; $year <= $lastYear; $year++) {
            for ($month = 1; $month <= 12; $month++) {
                $eventsForMonth = count(User::
                where('spaceID', $spaceID)
                    ->whereYear('created_at', ( $year ) )
                    ->whereMonth('created_at', ( $month ) )
                    ->get()
                );
                if ( !empty($eventsForMonth) ) {
                    if (array_key_exists("$month-$year", $events))
                        $events["$month-$year"] += $eventsForMonth;
                    else
                        $events["$month-$year"] = $eventsForMonth;

                }

            }
        }
        $labels = [];
        $series = [];

        foreach ($events as $key => $event) {
            array_push($labels, $key);
            array_push($series, $event);
        }
        return [
            'labels' => $labels,
            'series' => [$series],
        ];
    }

    public function getAppearancesForMonthYear($spaceID, $startMonth, $startYear, $endMonth, $endYear) {
        $start = date('YYYY-MM-DD HH:MM:SS', mktime(0, 0, 0, $startMonth, 1, $startYear));
        $end = date('YYYY-MM-DD HH:MM:SS', mktime(0, 0, 0, ($endMonth + 1), 1, $endYear));

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
                'check-ins' => $appearance,
            ]);
        }
        return $appearancesArray;
    }
    
    public function getUserSignUps($spaceID, $month, $year, $day, $endMonth, $endYear, $endDay) {
        $start = date('Y-m-d G:i:s', mktime(0, 0, 0, $month, $day, $year));
        $end = date('Y-m-d G:i:s', mktime(23, 59, 59, $endMonth, $endDay, $endYear));

        $sortedUsers = User::where('spaceID', $spaceID)
                ->whereBetween('created_at', [$start, $end])
                ->orderBy('created_at', 'ASC')
                ->get();

        if (count($sortedUsers) == 0)
            return ['error' => 'No users signed up on that day'];
        
        $users = [];
        foreach ($sortedUsers as $user) {
            array_push($users, [
                'time' => $user->created_at,
                'name' => $user->name,
                'email' => $user->email,
            ]);
        }

        return ['users' => $users];
    }

    public function getCustomerSignUps($spaceID, $month, $year, $day, $endMonth, $endYear, $endDay) {
        $start = mktime(0, 0, 0, $month, $day, $year);
        $end = mktime(23, 59, 59, $endMonth, $endDay, $endYear);
        $space = Workspace::find($spaceID)->makeVisible('stripe');
        $subscriptionService = new SubscriptionService($space->stripe);
        $users = $subscriptionService->getAllCustomersFromDateRange($start, $end);
        return ['users' => $users];
    }
    
    public function getUserCheckins($spaceID, $month, $year, $day, $endMonth, $endYear, $endDay) {
        $start = date('Y-m-d G:i:s', mktime(0, 0, 0, $month, $day, $year));
        $end = date('Y-m-d G:i:s', mktime(23, 59, 59, $endMonth, $endDay, $endYear));
        $sortedAppearances = Appearance::where('spaceID', $spaceID)
                ->whereBetween('created_at', [$start, $end])
                ->orderBy('created_at', 'ASC')
                ->get();

        if (count($sortedAppearances) == 0)
            return ['error' => 'No check-ins found'];

        $users = [];
        foreach ($sortedAppearances as $appearance) {
           $user = User::find($appearance->userID);
            array_push($users, [
                'time' => $appearance->created_at,
                'name' => $user->name,
                'email' => $user->email,
                'occasion' => $appearance->occasion,
                'eventID' => $appearance->eventID,
                'userID' => $appearance->userID
            ]);
        }

        return ['users' => $users];
    }

    /**
     * @param $spaceId
     * @return events@spaceID
     */
    public function getEventAppearances($spaceID) {
        // event
        $sortedAppearances = Appearance::where('spaceID', $spaceID)
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
                                           where('spaceID', $spaceID)
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
        $appearances = Appearance::where('spaceID', $spaceID)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->get();
            return $appearances;
    }
}
