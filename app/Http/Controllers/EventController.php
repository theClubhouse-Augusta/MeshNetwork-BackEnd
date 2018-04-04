<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Response;
use Purifier;
use Hash;
use Auth;
use JWTAuth;
use DateTime;

use App\Eventz;
use App\Event;
use App\Eventskill;
use App\Eventdate;
use App\Eventorganizer;
use App\Sponser;
use App\Skill;
use App\Sponserevent;
use App\User;
use App\Workspace;
use App\Calendar;
use App\Opt;
use App\File;
use App\Challenge;
use App\Upload;
use Carbon\Carbon;
use DB;
use MaddHatter\LaravelFullcalendar\Facades\Calendar as Fullcalendar;

class EventController extends Controller
{

    /** JWTAuth for Routes
     * @param void
     * @return void
     */
    public function __construct()
    {
        $this->middleware('jwt.auth', ['only' => [
            'get',
            'attend',
            'store',
            'search',
            'opt',
            'getCalendar',
            'storeCalendar',
            'deleteCalendar',
            'deleteEvent',
            'updateEvent',
            'Sponsers',
        ]]);
    }

    public function eventzs() {
        $events = [];
        $data = Eventz::all();
        if($data->count()) {
            foreach ($data as $key => $value) {
                $cal = new Fullcalendar();
                $cal::setOptions([
                    'defaultDate' => '2017-09-01',
                    'editable' => true,
                    'navLinks' => false,
                ]);
                $event = $cal::event(
                    $value->title,
                    true,
                    new \DateTime($value->start_date),
                    new \DateTime($value->end_date.' +1 day'),
                    null,
                    // Add color and link on event
                    [
                        'color' => '#ff0000',
                        'url' => '',
                    ]
                );

                $events[] = $event;
            }
        }
        $event = Fullcalendar::event(
            null,
            true,
            new \DateTime('2017-09-01'),
            new \DateTime('2017-09-02'),
            null,
            // Add color and link on event
            [
                'color' => '#ff0000',
                'url' => '',
                'overlap' => false,
                'rendering' => 'background',
                'color' => '#ff9f89'
            ]
        );
        $events[] = $event;
        $calendar = Fullcalendar::addEvents($events);
        return view('fullcalendar', compact('calendar'));
    }
    public function store(Request $request) 
    {
        // user currently signed in
        $userID = Auth::id();
        $spaceID = User::find($userID)->spaceID;
        $rules = [
            'name' => 'required|string',
            'tags' => 'required|string',
            'organizers' => 'required|string',
            'sponsors' => 'nullable|string',
            'newSponsors' => 'nullable|string',
            'description' => 'required|string',
            'eventID' => 'nullable|string',
            'city' => 'nullable|string',
            'address' => 'nullable|string',
            'state' => 'nullable|string',
        ];

        // Validate input against rules
        $validator = Validator::make(Purifier::clean($request->all()), $rules);

        if ($validator->fails()) {
            return Response::json(['error' => 'You must fill out all fields!']);
        }

        $deleteEventID = $request['eventID'];
        if (!empty($deleteEventID)) {
            $event = Event::find($deleteEventID);
            $success = $event->delete();
            if (!$success) {
                return Response::json(['error' => 'Database error']);
            }
        }

        $sponsors = $request->input('sponsors');
        $event = new Event;
        $sponserIDs = [];
        if (!empty($sponsors)) {
            $sponsorArray = explode(',', $sponsors);
            foreach ($sponsorArray as $s) {
                $sponsor = Sponser::where('name', $s)->first();
                array_push($sponserIDs, $sponsor->id);
            }
        }

        $newSponsors = json_decode($request->input('newSponsors'));
        if (!empty($newSponsors)) {
            foreach ($newSponsors as $key => $s) {
                $name = $s->name;
                $website = $s->website;
                $sponser = new Sponser;
                $sponser->name = $name;
                $sponser->website = $website;
                $logo = $request->file('logos' . $key);
                $logoName = $logo->getClientOriginalName();
                $logo->move('storage/logo/', $logoName);
                $sponser->logo = $request->root() . '/storage/logo/' . $logoName;
                $sponser->save();
                $id = $sponser->id;
                array_push($sponserIDs, $id);
            }
        }

        /* Event Info */
        $title = $request->input('name');
        $description = $request->input('description');
        $tags = explode(',', $request->input('tags'));
        $organizers = explode(',', $request->input('organizers'));
        $dates = json_decode($request->input('dates'));

        // optional input
        $url = $request->input('url');
        $city = $request->input('city');
        $address = $request->input('address');
        $state = $request->input('state');

        // create ne App\Event
        $event = new Event;
        $event->userID = $userID;
        $event->spaceID = $spaceID;
        $event->title = $title;
        count($dates) > 1 ? $event->multiday = 1 : $event->multiday = 0;
        $event->description = $description;
        
        if ( (!empty($city) && !empty($state) && !empty($address)) ) {
            $event->city = $city;
            $event->address = $address;
            $event->state = $state;
            $coordinates = $this->getGeoLocation($address, $city, $state);
            $lon = $coordinates->results[0]->geometry->location->lng;
            $lat = $coordinates->results[0]->geometry->location->lat;
            $event->lon = $lon;
            $event->lat = $lat;
        }
        $event->url = $url;

        if (!$event->save())
            return Response::json(['error' => 'Database error']);

        $eventID = $event->id;

        // event organizers
        if (!empty($organizers)) {
            foreach ($organizers as $organizer) {
                $eventorganizer = new Eventorganizer;
                $eventorganizer->eventID = $eventID;
                $user = User::where('email', $organizer)->first();
                $eventorganizer->userID = $user->id;
                if (!$eventorganizer->save()) return Response::json(['error' => 'e org']);
                $check = Calendar::where('eventID', $eventID)->where('userID', $eventorganizer->userID)->first();
                if (empty($check)) {
                    $calendar = new Calendar;
                    $calendar->userID = $eventorganizer->userID;
                    $calendar->eventID = $eventID;
                    $calendar->save();
                }
            }
        }

        // Update App\Eventskill;
        if (!empty($tags)) {
            foreach ($tags as $tag) {
                $skillTag = Skill::where('name', $tag)->first();
                // Create new EventSkill
                $eventSkill = new Eventskill;
                $eventSkill->eventID = $eventID;
                $eventSkill->skillID = $skillTag->id;
                $eventSkill->name = $skillTag->name;
                // Persist App\Skill to database
                if (!$eventSkill->save())
                    return Response::json(['error' => 'eventSkill database error']);

            }
        }

        // date placeholder
        foreach ($dates as $date) {
            $start = $date->start;
            $end = $date->end;
            $ymd = explode('-', $date->day);
            $hms = explode(':', $start);
            $hme = explode(':', $end);

            $startStamp = date('Y-m-d H:i:s', mktime(
                (int)$hms[0],
                (int)$hms[1],
                0,
                (int)$ymd[1],
                (int)$ymd[2],
                (int)$ymd[0]
            ));

            $endStamp = date('Y-m-d H:i:s', mktime(
                (int)$hme[0],
                (int)$hme[1],
                0,
                (int)$ymd[1],
                (int)$ymd[2],
                (int)$ymd[0]
            ));
            $eventDate = new Eventdate;
            $eventDate->eventID = $eventID;
            $eventDate->start = $startStamp;
            $eventDate->end = $endStamp;
            if (!$eventDate->save()) return Response::json(['error' => 'evenDate error']);
        } 
        if (!empty($sponserIDs)) {
            foreach ($sponserIDs as $sponserID) {
                $sponserevent = new Sponserevent;
                $sponserevent->eventID = $eventID;
                $sponserevent->sponserID = $sponserID;
                if (!$sponserevent->save()) return Response::json(['error' => 'sponserevent error']);
            }
        }

        $check = Calendar::where('eventID', $eventID)->where('userID', $userID)->first();
        if (empty($check)) {
            $calendar = new Calendar;
            $calendar->userID = $userID;
            $calendar->eventID = $eventID;
            $calendar->save();
        }
        return Response::json(['success' => 'Event Added!', 'eventID' => $eventID]);
    }

    // all events all spaces
    public function get() {
        $now = date();
        $events = $Event::where('start' > $now)->get();

        if (empty($events)) {
            return Response::json(['error' => 'No Events']);
        }
        return Response::json(['success' => $events]);
    }

    public function updateEvent(Request $request) 
    {
        $userID = Auth::id();
        $spaceID = User::find($userID)->spaceID;
        $rules = [
            'eventID' => 'required|string',
            'name' => 'required|string',
            'url' => 'required|string',
            'tags' => 'required|string',
            'organizers' => 'required|string',
            'sponsors' => 'nullable|string',
            'newSponsors' => 'nullable|string',
            'description' => 'required|string',
            'dates' => 'required|string',
        ];

        // Validate input against rules
        $validator = Validator::make(Purifier::clean($request->all()), $rules);

        if ($validator->fails()) {
            return Response::json(['error' => 'You must fill out all fields.']);
        }

        $eventID = $request->input('eventID');
        $event = Event::find($request['eventID']);

        $oldSponsors = Sponserevent::where('eventID', $event->id)->get();
        foreach($oldSponsors as $sKey => $sponsor)
        {
            $sponsor->delete();
        }

        $oldOrganizers = Eventorganizer::where('eventID', $event->id)->get();
        foreach($oldOrganizers as $sKey => $organizer)
        {
            $organizer->delete();
        }

        $oldSkills = Eventskill::where('eventID', $event->id)->get();
        foreach($oldSkills as $sKey => $skill)
        {
            $skill->delete();
        }

        $oldDates = Eventdate::where('eventID', $event->id)->get();
        foreach($oldDates as $sKey => $date)
        {
            $date->delete();
        }
        
        $sponsors = $request->input('sponsors');
        $sponserIDs = [];

        if (!empty($sponsors)) {
            $sponsorArray = explode(',', $sponsors);
            foreach ($sponsorArray as $s) {
                $sponsor = Sponser::where('name', $s)->first();
                if(!empty($sponsor)) {
                    array_push($sponserIDs, $sponsor->id);
                }
            }
        }

        $newSponsors = json_decode($request->input('newSponsors'));
        if (!empty($newSponsors)) {
            foreach ($newSponsors as $key => $s) {
                $name = $s->name;
                $website = $s->website;
                $sponser = new Sponser;
                $sponser->name = $name;
                $sponser->website = $website;
                $logo = $request->file('logos' . $key);
                $logoName = $logo->getClientOriginalName();
                $logo->move('storage/logo/', $logoName);
                $sponser->logo = $request->root() . '/storage/logo/' . $logoName;
                $sponser->save();
                $id = $sponser->id;
                array_push($sponserIDs, $id);
            }
        }

        /* Event Info */
        $title = $request->input('name');
        $description = $request->input('description');
        $tags = explode(',', $request->input('tags'));
        $organizers = explode(',', $request->input('organizers'));
        $dates = json_decode($request->input('dates'));
        
        $url = $request->input('url');
        $event->title = $title;
        count($dates) > 1 ? $event->multiday = 1 : $event->multiday = 0;
        $event->description = $description;
        $event->url = $url;

        if (!$event->save())
            return Response::json(['error' => 'Database error']);

        // event organizers
        if (!empty($organizers)) {
            foreach ($organizers as $organizer) {
                $eventorganizer = new Eventorganizer;
                $eventorganizer->eventID = $eventID;
                $user = User::where('email', $organizer)->first();
                if(!empty($user)) 
                {
                    $eventorganizer->userID = $user->id;
                    if (!$eventorganizer->save()) return Response::json(['error' => 'e org']);
                    $check = Calendar::where('eventID', $eventID)->where('userID', $eventorganizer->userID)->first();
                    if (empty($check)) {
                        $calendar = new Calendar;
                        $calendar->userID = $eventorganizer->userID;
                        $calendar->eventID = $eventID;
                        $calendar->save();
                    }
                }
            }
        }

        // Update App\Eventskill;
        if (!empty($tags)) {
            foreach ($tags as $tag) {
                $skillTag = Skill::where('name', $tag)->first();
                // Create new EventSkill
                $eventSkill = new Eventskill;
                $eventSkill->eventID = $eventID;
                $eventSkill->skillID = $skillTag->id;
                $eventSkill->name = $skillTag->name;
                // Persist App\Skill to database
                if (!$eventSkill->save())
                    return Response::json(['error' => 'eventSkill database error']);

            }
        }

        // date placeholder
        foreach ($dates as $date) {
            $start = $date->start;
            $end = $date->end;
            $ymd = explode('-', $date->day);
            $hms = explode(':', $start);
            $hme = explode(':', $end);

            $startStamp = date('Y-m-d H:i:s', mktime(
                (int)$hms[0],
                (int)$hms[1],
                0,
                (int)$ymd[1],
                (int)$ymd[2],
                (int)$ymd[0]
            ));

            $endStamp = date('Y-m-d H:i:s', mktime(
                (int)$hme[0],
                (int)$hme[1],
                0,
                (int)$ymd[1],
                (int)$ymd[2],
                (int)$ymd[0]
            ));
            $eventDate = new Eventdate;
            $eventDate->eventID = $eventID;
            $eventDate->start = $startStamp;
            $eventDate->end = $endStamp;
            if (!$eventDate->save()) return Response::json(['error' => 'evenDate error']);
        } 
        if (!empty($sponserIDs)) {
            foreach ($sponserIDs as $sponserID) {
                $sponserevent = new Sponserevent;
                $sponserevent->eventID = $eventID;
                $sponserevent->sponserID = $sponserID;
                if (!$sponserevent->save()) return Response::json(['error' => 'sponserevent error']);
            }
        }

        $check = Calendar::where('eventID', $eventID)->where('userID', $userID)->first();
        if (empty($check)) {
            $calendar = new Calendar;
            $calendar->userID = $userID;
            $calendar->eventID = $eventID;
            $calendar->save();
        }
        return Response::json(['success' => 'Event Updated!', 'eventID' => $eventID]);


    }

    // show event.id
    public function show($eventID)
    {
        $event = Event::find($eventID);
        $tags = $this->getTags($eventID);
        $sponsors = $this->getSponsors($eventID);
        $organizers = $this->getOrganizers($eventID);
        $attendees = $this->getAttendees($eventID);
        $challenges = Challenge::where('eventID', $eventID)->get();
        $upcomingEvents = $this->getUpcoming();
        $workSpace = Workspace::find($event->spaceID);
        $dates = Eventdate::where('eventID', $eventID)->get();
        foreach ($dates as $key => $date) {
            $date->startFormatted = Carbon::createFromTimeStamp(strtotime($date->start))->format('l jS \\of F Y h:i A');
            $date->endFormatted = Carbon::createFromTimeStamp(strtotime($date->end))->format('l jS \\of F Y h:i A');
        }

        if (empty($event)) {
            return Response::json(['error' => 'Could not find event']);
        }

        //$challenge = $event->challenge;

        foreach($challenges as $key => $challenge)
        {
            $challengeFiles = Upload::where('challengeID', $challenge->id)->get();
            $challenge->challengeFiles = $challengeFiles;
        }

        return Response::json([
            'event' => $event,
            'workspace' => $workSpace,
            'upcomingEvents' => $upcomingEvents,
            'sponsors' => $sponsors,
            'organizers' => $organizers,
            'attendees' => $attendees,
            'challenges' => $challenges,
            'tags' => $tags,
            'dates' => $dates
        ]);

        /*if (!$challenge) {
            $workSpace = Workspace::find($event->spaceID);
            return Response::json([
                'event' => $event,
                'local' => $workSpace,
                'upcomingEvents' => $upcomingEvents,
                'sponsors' => (count($sponsors) != 0) ? $sponsors : false,
                'organizers' => $organizers,
                'attendees' => !empty($attendees) ? $attendees : false,
                'tags' => $tags
            ]);
        }   elseif ($challenge) {
            $sponsors = $this->getSponsors($event->id);
            $hostSpace = Workspace::find($event->spaceID);
            $participatingSpaces = $this->getParticipating($eventID);
            array_push($participatingSpaces, $hostSpace);
            return Response::json([
                'event' => $event,
                'hostSpace' => $hostSpace,
                'nonLocal' => $participatingSpaces ? $participatingSpaces : false,
                'upcomingEvents' => $upcomingEvents,
                'sponsors' => (count($sponsors) != 0) ? $sponsors : false,
                'organizers' => $organizers,
                'attendees' => !empty($attendees) ? $attendees : false,
                'tags' => $tags
            ]);
        }*/
    }

    private function getParticipating($eventID)
    {
        $opts = Opt::where('eventID', $eventID)->get();

        $workSpaces = array();
        foreach ($opts as $opt) {
            $space = Workspace::find($opt->spaceID);
            array_push($workSpaces, $space);
        }
        return $workSpaces;
    }

    private function getUpcoming()
    {
        $upcoming = array();
        $eventdates = Eventdate::all();
        foreach ($eventdates as $eventdate) {
            $now = new DateTime();
            $eDate = new DateTime($eventdate->start);
            $diff = $now->diff($eDate);
            $formattedDiff = $diff->format('%R%a days');

            if ((int)$formattedDiff > 0) {
                $event = Event::find($eventdate->eventID);
                $space = Workspace::find($event->spaceID);
                array_push(
                    $upcoming,
                    [
                        "title" => $event->title,
                        "id" => $event->id,
                        "start" => $eventdate->start,
                        "end" => $eventdate->end,
                        'name' => $space->name
                    ]
                );
                if (count($upcoming) === 3) {
                    return $upcoming;
                }
            }
        }
        return $upcoming;
    }

    private function getUp($spaceID)
    {
        $upcoming = array();
        $eventdates = Eventdate::all();
        foreach ($eventdates as $key => $eventdate) {
            $now = new DateTime();
            $eDate = new DateTime($eventdate->start);
            $diff = $now->diff($eDate);
            $formattedDiff = $diff->format('%R%a days');

            if ((int)$formattedDiff > 0) {
                $event = Event::where('id', $eventdate->eventID)
                    ->where('spaceID', $spaceID)
                    ->first();
                if (!empty($event)) {
                    array_push(
                        $upcoming,
                        [
                            "title" => $event->title,
                            "id" => $event->id,
                            "start" => $eventdate->start,
                            "end" => $eventdate->end,
                            "description" => $event->description
                        ]
                    );
                }
                if (count($upcoming) === 5) {
                    return $upcoming;
                }
            }
        }
        return $upcoming;
    }

    public function upcoming($spaceID)
    {
        $events = $this->getUp($spaceID);
        return Response::json($events);
    }

    private function getSponsors($eventID)
    {
        $sponsors = [];
        $sponsorevents = Sponserevent::where('eventID', $eventID)->get();
        if (!empty($sponsorevents)) {
            foreach ($sponsorevents as $sponsorevent) {
                $sponsor = Sponser::where('id', $sponsorevent->sponserID)->first();

                if (count($sponsor) !== 0) {
                    array_push($sponsors, $sponsor);
                }
            }
        }
        return $sponsors;
    }

    private function getOrganizers($eventID)
    {
        $organizers = [];
        $eventorganizers = Eventorganizer::where('eventID', $eventID)->get();
        if (!empty($eventorganizers)) {
            foreach ($eventorganizers as $eventorganizer) {
                $organizer = User::find($eventorganizer->userID);
                array_push($organizers, $organizer);
            }
        }
        return $organizers;
    }

    private function getTags($eventID)
    {
        $tags = [];
        $eventTags = Eventskill::where('eventID', $eventID)->get();
        if (!empty($eventTags)) {
            foreach ($eventTags as $eventTag) {
                array_push($tags, $eventTag->name);
            }
        }
        return $tags;
    }

    private function getAttendees($eventID)
    {
        $attendees = [];
        $eventattendees = Calendar::where('eventID', $eventID)->get();
        if (!empty($eventattendees)) {
            foreach ($eventattendees as $eventattendee) {
                $attendee = User::find($eventattendee->userID);
                if (!empty($attendee)) {
                    array_push($attendees, $attendee);
                }
            }
        }
        return $attendees;
    }

    public function search(Request $request)
    {
        $organizer = Auth::user();
        if ($organizer->roleID != 2)
            return Reponse::json(['error' => 'invalid crediantials']);
        $rules = [
            'query' => 'required|string',
        ];

        // Validate input against rules
        $validator = Validator::make(
            Purifier::clean(
                $request->all()
            ),
            $rules
        );

        if ($validator->fails()) {
            return Response::json(['error' => 'You must fill out all fields.']);
        }

        $query = $request->input('query');


        $search = Event::where('title', 'LIKE', $query)
            ->Orwhere('description', 'LIKE', $query)
            ->get();

        if (!empty($search)) {
            return Response::json($search);
        }
        return Response::json(['error' => 'Nothing matched your query']);
    }


    // allow workspaces to opt-in to a remote event at another workspace
    public function opt(Request $request)
    {
        $rules = [
            'spaceID' => 'required|string',
            'eventID' => 'required|string'
        ];

        // Validate input against rules
        $validator = Validator::make(Purifier::clean($request->all()), $rules);

        if ($validator->fails()) {
            return Response::json(['error' => 'You must fill out all fields.']);
        }

        // form input
        $spaceID = $request->input('spaceID');
        $eventID = $request->input('eventID');

        $opt = new Opt;
        $opt->spaceID = $spaceID;
        $opt->eventID = $eventID;

        if (!$opt->save()) {
            return Response::json(['error' => 'Database error']);
        }
        return Response::json(['success' => 'Joined event!']);
    }

    // delete event
    public function deleteEvent($eventID)
    {
        $user = Auth::user();
        $event = Event::find($eventID);
        $space = Workspace::where('id', $event->spaceID)->first();

        if($user->spaceID != $space->id && $user->roleID != 2)
        {
            return Response::json(['error' => 'You do not have permission.']);
        }

        if (empty($event)) {
            return Response::json(['error' => 'No event with ' . $eventID]);
        }

        $event->delete();

        return Response::json(['success' => 'Event Deleted.']);
    }

    public function storeCalendar($eventID)
    {
        $userID = Auth::id();

        $calendar = new Calendar;
        $calendar->userID = $userID;
        $calendar->eventID = $eventID;

        $check = Calendar::where('eventID', $calendar->eventID)->where('userID', $calendar->userID)->first();

        if (!empty($check)) {
            return Response::json(['duplicate' => 'You already signed up for this event']);
        }

        if (!$calendar->save()) {
            return Response::json(['error' => 'Database error']);
        }
        return Response::json(['success' => 'Event aded to calendar']);
    }

    // get signed in users events on calendar
    public function getCalendar()
    {
        $userID = Auth::id();
        // $userID = 1;
        $calendars = Calendar::where('userID', $userID)->get();

        $events = array();
        foreach ($calendars as $key => $calendar) {
            $eventID = $calendar->eventID;
            $event = Event::find($eventID);

            if (!empty($event)) {
                array_push($events, $event);
            }
        }

        if (count($events) == 0) {
            return Response::json(['error' => 'No events scheduled']);
        }
        return Response::json($events);
    }

    // delete event from signed in users calendar
    public function deleteCalendar($eventID)
    {
        $userID = Auth::id();
        $calendar = Calendar::where('userID', $userID)
            ->orWhere('eventID', $eventID)
            ->first();

        if (empty($calendar)) {
            return Response::json(['error' => 'Event not on calendar']);
        }

        if (!$calendar->delete()) {
            return Response::json(['error' => 'Database error']);
        }
        return Response::json(['success' => 'Event removed form calendar']);
    }

    public function attend($eventID)
    {
        $userID = Auth::id();
        $event = Event::find($eventID);

        $check = Calendar::where('eventID', $eventID)->where('userID', $userID)->first();
        if(!empty($check))
        {
            return Response::json(['duplicate' => 'You are already attending this event.']);
        }
        $attendEvent = new Calendar;
        $attendEvent->eventID = $eventID;
        $attendEvent->userID = $userID;
        $attendEvent->save();

        return Response::json(['success' => 'Thanks for Attending this event!']);
    }

    public function Sponsers()
    {
        $sponsers = Sponser::all();
        $sponsersArray = [];
        foreach ($sponsers as $sponser) {
            array_push($sponsersArray, $sponser->name);
        }
                // 'label' => $sponser->name,
                // 'value' => $sponser->name,
                // 'id' => $sponser->id,
                // 'logo'=> $sponser->logo,
                // 'website'=> $sponser->website,
        return Response::json($sponsersArray);
    }

    public function EventOrganizers($id)
    {
        $event = Event::find($id);
        $organizers = Eventorganizer::where('eventorganizers.eventID', $event->id)->join('users', 'eventorganizers.userID', '=', 'users.id')->select('users.id', 'users.name', 'users.roleID', 'users.spaceID', 'users.title', 'users.avatar')->get();

        return Response::json($organizers);
    }

    public function EventDates($id)
    {
        $event = Event::find($id);
        $dates = Eventdate::where('eventID', $event->id)->get();

        $event->dates = $dates;

        return Response::json($event);
    }

    public function getTodaysEvents($spaceID)
    {
        /*$user = Auth::user();
        $spaceID = $user->spaceID;
        $date = new DateTime("now");
        $today = $date->format('Y-m-d');
        $events = EventDate::all();
        $eventsArray = [];
        foreach ($events as $event) {
            $start = $event->start;
            $pos = strrpos($start, " ");
            $formattedDate = substr($start, 0, $pos);
            if ($today == $formattedDate) {
                $findEvent = Event::find($event->eventID);
                if ($findEvent->spaceID == $spaceID)
                    array_push($eventsArray, $findEvent);
            }
        }
        return Response::json($eventsArray);*/
        $events = DB::table('eventdates')
            ->select(DB::raw('*'))
            ->whereRaw('Date(eventdates.start) = CURDATE()')
            ->join('events', 'eventdates.eventID', '=', 'events.id')
            ->where('events.spaceID', $spaceID)
            ->select('events.id', 'events.spaceID', 'events.title', 'events.description', 'events.image', 'eventdates.start')
            ->get();
        foreach ($events as $key => $event) {
            $event->start = Carbon::createFromTimeStamp(strtotime($event->start))->format('l jS \\of F Y h:i A');
        }
        return Response::json($events);
    }

    public function getDashboardEvents($spaceID)
    {
        $events = Event::where('spaceID', $spaceID)->orderBy('created_at', 'desc')->get();

        foreach ($events as $key => $event) {
            $date = Eventdate::where('eventID', $event->id)->first();
            if(!empty($date)) {
              $date = Carbon::createFromTimeStamp(strtotime($date->start))->format('l jS \\of F Y');
            } else {
              $date = "No Start Date";
            }
            $event->date = $date;

            $space = Workspace::find($event->spaceID);
            $event->space = $space;
        }

        return Response::json($events);
    }

    public function resetPassword(Request $request) {
        // Validation Rules
        $rules = [
            'email' => 'required|string',
        ];
        // Validate input against rules
        $validator = Validator::make(Purifier::clean($request->all()), $rules);

        if ($validator->fails()) {
            return Response::json(['error' => 'You must fill out all fields.']);
        }

        $email = $request['email'];
        $user = User::where('email', $email)->first();
        $space = Workspace::where('id', $user->spaceID)->first();
        $temp = str_random(12);
        $user->password = $temp;
        $user->save();

        try {
            Mail::send(
                'emails.resetPassword',
                array('temp' => $temp),
                function ($message) use ($user, $space) {
                    $message->from($space->email, $space->name);
                    $message->to($user->email, $user->name)->subject($space->name . ': Password reset for ' . $space->name . '@innovationmesh.com ');
                }
            );
            return Response::json(['success' => 'Check your email for your temporary password.']);
        } catch (Exception $exception) {
            return Response::json($exception);
        }
    }

    private function getGeoLocation($address, $city, $state)
    {
        $address_array = explode(' ', $address);

        $length = count($address_array);

        $URIparam = '';
        for ($i = 0; $i < $length; $i++) {
            if ($i != ($length - 1))
                $URIparam .= $address_array[$i] . '+';
            else
                $URIparam .= $address_array[$i];
        }
        return json_decode(file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address=' . $URIparam . ',+' . $city . ',+' . $state . '&key=AIzaSyCrhrhhqlvkuQkAycbZzVS5f-ym_tpFs0o'));
    }
}
