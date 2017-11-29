<?php
namespace App\Services;
use DateTime;
use App\User;
class JoinsService {
    /**
     * Get all member signUps
     * @param workspace.id 
     * @return Illuminate\Support\Facades\Response::class
     */
    public function spaceUserJoins($spaceId, $year) 
    {
        $yearStart = new DateTime($year);
        $yearStartFormat = $yearStart->format('Y');
        // $user = User::find( Auth::id() )->spaceID;
        $sortedUsers = User::where('spaceID', $spaceId)
                            ->orderBy('created_at', 'ASC')
                            ->get();
        $res = array();
        foreach ($sortedUsers as $sorted) 
        {
            $check = new DateTime($sorted->created_at);
            $yearCheck = $check->format('m');
            if ($yearCheck) {
                array_push($res, $yearCheck);
            }
        }
        return $res;                    
        $memberCount = count($sortedUsers);

        if ( !empty($memberCount) ) {
            $firstUser = $sortedUsers[0]->created_at;
            $firstYear = $firstUser->year;
            $firstMonth = $firstUser->month;

            $lastUser = $sortedUsers[( $memberCount - 1 )]->created_at;
            $lastYear = $lastUser->year;
            $lastMonth = $lastUser->month;

            $yearSpan = (int)$lastYear - (int)$firstYear;

            $joins = array();
            
            for ($year = 0; $year <= $yearSpan; $year++) {
                for ($month = 1; $month <= 12; $month++) {
                    $joinsForMonth = count(User::
                                         where('spaceID', $spaceId)
                                         ->whereYear('created_at', ( $firstYear + $year ) )
                                         ->whereMonth('created_at', ( $month ) )
                                         ->get()
                                     ); 
                    if ( !empty($joinsForMonth) )  {
                        array_push($joins, $joinsForMonth);
                    }
                }        
            }
            return (
                array (
                    'memberSignUpData' => $joins,
                    'firstYear' => $firstYear, 
                    'lastYear' => $lastYear, 
                    'firstMonth' => $firstMonth,
                    'lastMonth' => $lastMonth 
                )
            );
        }
    }
}