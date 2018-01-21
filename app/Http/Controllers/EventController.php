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
use Carbon\Carbon;
use DB;

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
            'update',
            'search',
            'opt',
            'getCalendar',
            'storeCalendar',
            'deleteCalendar',
            'delete',
            'Sponsers',
        ]]);
    }

    public function store(Request $request) {
        // user currently signed in
        $userID = Auth::id();
        $spaceID = User::find($userID)->spaceID;
        // return $request->day;

        if ($request->day) {
            $rules = [
               'compEvent' => 'required|string',
               'name' => 'required|string',
               'url' => 'required|string',
               'tags' => 'required|string',
               'organizers' => 'required|string',
               'sponsors' => 'nullable|string',
               'newSponsors' => 'nullable|string',
               'day' => 'required|string',
               'start' => 'required|string',
               'end' => 'required|string',
               'description' => 'required|string',
               'image' => 'required|string',
            ];
        }   else {
            $rules = [
                'compEvent' => 'required|string',
                'name' => 'required|string',
                'url' => 'required|string',
                'tags' => 'required|string',
                'organizers' => 'required|string',
                'sponsors' => 'nullable|string',
                'newSponsors' => 'nullable|string',
                'dateMulti' => 'required|string',
                'startMulti' => 'required|string',
                'endMulti' => 'required|string',
                'description' => 'required|string',
                'image' => 'required|string',

            ];
        }

        // Validate input against rules
        $validator = Validator::make(Purifier::clean($request->all()), $rules);

        if ($validator->fails()) {
            return Response::json(['error' => 'You must fill out all fields!']);
        }

        $event = new Event;
        /* Sponser Info */
        $sponsors = json_decode($request->input('sponsors'));
        $sponserIDs = [];

        if (!empty($sponsors))  {
            foreach($sponsors as $s) {
                array_push($sponserIDs, $s->id);
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
                $logo = $request->file('logos'.$key);
                $logoName = $logo->getClientOriginalName();
                $logo->move('storage/logo/', $logoName);
                $sponser->logo = $request->root().'/storage/logo/'.$logoName;
                $sponser->save();
                $id = $sponser->id;
                array_push($sponserIDs, $id);
            }
        }

        /* Event Info */
        $challenge = json_decode($request->input('compEvent'));
        $title = $request->input('name');
        $description = $request->input('description');
        $tags = json_decode($request->input('tags'));
        $organizers = json_decode($request->input('organizers'));
        // optional input
        $url = $request->input('url');
        $files = $request->input('file0');
        $image = $request->file('image');
        $imageName = $image->getClientOriginalName();
        $image->move('/storage/events/images', $imageName);

        // create ne App\Event
        $event = new Event;
        $event->userID = $userID;
        $event->spaceID = $spaceID;
        $event->title = $title;

        $event->description = $description;
        $event->challenge = $challenge;
        $event->url = $url;
        $event->image = $request->root().'/storage/events/images/'.$imageName;
        $day = json_decode($request->input('day'));

        if (!empty($day)) {
            $event->multiday = false;
        }   else {
            $event->multiday = true;
        }

        if (!$event->save()) return Response::json([ 'error' => 'Database error' ]);
        $eventID = $event->id;

        // event organizers
        if (!empty($organizers)) {
            foreach ($organizers as $organizer) {
                $eventorganizer = new Eventorganizer;
                $eventorganizer->eventID = $eventID;
                $eventorganizer->userID = $organizer->value;
                if (!$eventorganizer->save()) return Response::json([ 'error' => 'e org' ]);
                $check = Calendar::where('eventID', $eventID)->where('userID', $eventorganizer->userID)->first();
                if (empty($check)) {
                    $calendar = new Calendar;
                    $calendar->userID = $eventorganizer->userID;
                    $calendar->eventID = $eventID;
                    $calendar->save();
                }
            }
        }

        // Update App\Skill;
        if (!empty($tags)) {
            foreach($tags as $key => $tag) {
                if (!property_exists($tag, 'id')) {
                    $newSkill = new Skill;
                    $newSkill->name = $tag->value;
                    // Persist App\Skill to database
                    if (!$newSkill->save()) return Response::json([ 'error' => 'database error' ]);
                }
           }
        }

        // Update App\Eventskill;
        if (!empty($tags)) {
            foreach ($tags as $key => $tag) {
                $skillTag = Skill::where('name', $tag->value)->first();
                // Create new EventSkill
                $eventSkill = new Eventskill;
                $eventSkill->eventID = $eventID;
                $eventSkill->skillID = $skillTag->id;
                $eventSkill->name = $skillTag->name;
                // Persist App\Skill to database
                if (!$eventSkill->save())  return Response::json([ 'error' => 'eventSkill database error' ]);

            }
        }

        if (!empty($day)) {
            $start = json_decode($request->input('start'));
            $end = json_decode($request->input('end'));
            $ymd = explode('-', $day);
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
            $dateStart = $startStamp;
            $dateEnd = $endStamp;
            $eventDate->start= $dateStart;
            $eventDate->end = $dateEnd;
            if (!$eventDate->save()) return Response::json([ 'error' => 'evenDate error' ]);

        }   else {
            $dateMulti = json_decode($request->input('dateMulti'));
            $startMulti = json_decode($request->input('startMulti'));
            $endMulti = json_decode($request->input('endMulti'));

            foreach ($dateMulti as $key => $day) {
                $start = $startMulti[$key];
                $end = $endMulti[$key];
                $ymd = explode('-', $day->day);
                $hms = explode(':', $start->start);
                $hme = explode(':', $end->end);

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
                $dateStart = $startStamp;
                $dateEnd = $endStamp;
                $eventDate->start= $dateStart;
                $eventDate->end = $dateEnd;
                if (!$eventDate->save()) return Response::json([ 'error' => 'evenDate error' ]);
            }
        }

        if (!empty($sponserIDs)) {
            foreach ($sponserIDs as $sponserID) {
                $sponserevent = new Sponserevent;
                $sponserevent->eventID = $eventID;
                $sponserevent->sponserID = $sponserID;
                if (!$sponserevent->save()) return Response::json([ 'error' => 'sponserevent error' ]);
            }
        }

        // create new App\File;
        if (count($_FILES) != 0) {
            $length = count($_FILES);
            for($i = 0; $i < $length; $i++) {
                if (array_key_exists("files$i", $_FILES)) {
                    $file = new File;
                    $file->userID = $userID;
                    $file->eventID = $eventID;
                    $eventFile = $request->file('files'.$i);
                    $eventFileName = $eventFile->getClientOriginalName();
                    $eventFile->move("storage/events/$eventID/", $eventFileName);
                    $file->path = $request->root()."/storage/events/$eventID/$eventFileName";
                    if(!$file->save()) return Response::json([ 'error' => 'Database error' ]);
                }
            }
        }

        $check = Calendar::where('eventID', $eventID)->where('userID', $userID)->first();
        if (empty($check)) {
            $calendar = new Calendar;
            $calendar->userID = $userID;
            $calendar->eventID = $eventID;
            $calendar->save();
        }
        return Response::json($eventID);
    }

    // all events all spaces
    public function get()
    {

        $now = date();
        $events = $Event::where('start' > $now)->get();

        if (empty($events))
        {
            return Response::json([ 'error' => 'No Events' ]);
        }
        return Response::json([ 'success' => $events ]);
    }

    public function update(Request $request)
    {
        $rules = [
            'eventID' => 'required|string',
            'start' => 'nullable|string',
            'end' => 'nullable|string',
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'type' => 'nullable|string',
            'tags' => 'nullable|string',
            'local' => 'nullable|string',
            'file' => 'nullable|string'
        ];

        // Validate input against rules
        $validator = Validator::make(Purifier::clean($request->all()), $rules);

        if ($validator->fails()) {
            return Response::json(['error' => 'You must fill out all fields.']);
        }

        $eventID = $request->input('eventID');
        $start = $request->input('start');
        $end = $request->input('end');
        $title = $request->input('title');
        $description = $request->input('description');
        $type = $request->input('type');
        $tags = $request->input('tags');
        $local = $request->input('local');
        $file = $request->input('file');

        // check if another event is in time slot
        // $check = $start - $end;
        if (!$empty(check)) {
            return Response::json([ 'error' => 'Event already taking place during this time' ]);
        }

        // update Event
        $event = Event::find($eventID);
        if (!empty($start)) $event->start = $start;
        if (!empty($end)) $event->end = $end;
        if (!empty($title)) $event->title = $title;
        if (!empty($description)) $event->description = $description;
        if (!empty($type)) $event->type = $type;
        if (!empty($tags)) $event->tags = $tags;

        //optional input
        if (!empty($local)) $event->local = $local;

        if (!$event->save())
        {
            return Response::json([ 'error' => 'Database error' ]);
        }

        // create new App\File;
        if (!empty($file))
        {

            $eventID = $event->id;
            $userID = Auth::id();
            $files = explode(',', $file);

            foreach ($files as $key => $file)
            {
                $file = new File;
                $file->userID = $userID;
                $file->eventID = $eventID;
                // $file->path = TODO;
                if (!$file->save())
                {
                    return Response::json([ 'error' => 'Database error' ]);
                }
            }
        }
    }

    // show event.id
    public function show($eventID) {
        $event = Event::find($eventID);
        $tags = $this->getTags($eventID);
        $sponsors = $this->getSponsors($eventID);
        $organizers = $this->getOrganizers($eventID);
        $attendees = $this->getAttendees($eventID);
        $upcomingEvents = $this->getUpcoming();
        $workSpace = Workspace::find($event->spaceID);
        $dates = Eventdate::where('eventID', $eventID)->get();
        foreach($dates as $key => $date)
        {
          $date->start = Carbon::createFromTimeStamp(strtotime($date->start))->format('l jS \\of F Y h:i A');
          $date->end = Carbon::createFromTimeStamp(strtotime($date->end))->format('l jS \\of F Y h:i A');
        }

        if (empty($event)) {
            return Response::json([ 'error' => 'Could not find event' ]);
        }

        //$challenge = $event->challenge;

        return Response::json([
            'event' => $event,
            'workspace' => $workSpace,
            'upcomingEvents' => $upcomingEvents,
            'sponsors' => $sponsors,
            'organizers' => $organizers,
            'attendees' => $attendees,
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

    private function getUpcoming() {
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
                array_push($upcoming,
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

    private function getUp($spaceID) {
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
                    array_push($upcoming,
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

    public function upcoming($spaceID) {
        $events = $this->getUp($spaceID);
        return Response::json($events);
    }

    private function getSponsors($eventID)
    {
        $sponsors = [];
        $sponsorevents = Sponserevent::where('eventID', $eventID)->get();
        if (!empty($sponsorevents))
        {
            foreach($sponsorevents as $sponsorevent)
            {
                $sponsor = Sponser::where('id', $sponsorevent->sponserID)->first();

                if (count($sponsor) !== 0)
                {
                    array_push($sponsors, $sponsor);
                }
            }
        }
        return $sponsors;
    }

    private function getOrganizers($eventID)
    {
        $organizers= [];
        $eventorganizers = Eventorganizer::where('eventID', $eventID)->get();
        if (!empty($eventorganizers))
        {
            foreach($eventorganizers as $eventorganizer )
            {
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
        if (!empty($eventTags))
        {
            foreach($eventTags as $eventTag )
            {
                array_push($tags, $eventTag->name);
            }
        }
        return $tags;
    }

    private function getAttendees($eventID)
    {
        $attendees = [];
        $eventattendees = Calendar::where('eventID', $eventID)->get();
        if (!empty($eventattendees))
        {
            foreach($eventattendees as $eventattendee)
            {
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
        $rules = [
            'query' => 'required|string',
        ];

        // Validate input against rules
        $validator = Validator::make(
            Purifier::clean(
                $request->all()
            ), $rules
        );

        if ($validator->fails())
        {
            return Response::json(['error' => 'You must fill out all fields.']);
        }

        $query = $request->input('query');

        if (empty($query))
        {
            return Response::json([ 'error' => 'No search query recieved' ]);
        }

        $search = Event::where('title', 'LIKE', $query)
                        ->Orwhere('tags', 'LIKE', $query)
                        ->Orwhere('type', 'LIKE', $query)
                        ->Orwhere('description', 'LIKE', $query)
                        ->get();

        if (!empty($search))
        {
            return Response::json([ 'success' => $search ]);
        }
        return Response::json([ 'error' => 'Nothing matched your query' ]);
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

        if ($validator->fails())
        {
            return Response::json(['error' => 'You must fill out all fields.']);
        }

        // form input
        $spaceID = $request->input('spaceID');
        $eventID = $request->input('eventID');

        $opt = new Opt;
        $opt->spaceID = $spaceID;
        $opt->eventID = $eventID;

        if (!$opt->save())
        {
            return Response::json([ 'error' => 'Database error' ]);
        }
        return Response::json([ 'success' => 'Joined event!' ]);
    }

    // delete event
    public function delete($eventID)
    {
        $event = Event::find($eventID);

        if (empty($event))
        {
            return Response::json([ 'error' => 'No event with '.$eventID ]);
        }

        $event->delete();

        $calendars = Calendar::where('eventID', $eventID)->get();

        if (!empty($calendars))
        {
            foreach ($calendars as $key => $calendar)
            {
                $calendar->delete();
            }
        }

        $opts = Opt::where('eventID', $eventID)->get();
        if (!empty($opts))
        {
            foreach ($opts as $key => $opt)
            {
                $opt->delete();
            }
        }

        $files = File::where('eventID', $eventID);

        if (!empty($files))
        {
            foreach ($files as $key => $file)
            {
                $file->delete();
            }
        }
    }

    public function storeCalendar($eventID)
    {
        $userID = Auth::id();

        $calendar = new Calendar;
        $calendar->userID = $userID;
        $calendar->eventID = $eventID;

        $check = Calendar::where('eventID', $calendar->eventID)->where('userID', $calendar->userID)->first();

        if (!empty($check))
        {
            return Response::json([ 'duplicate' => 'You already signed up for this event' ]);
        }

        if (!$calendar->save())
        {
            return Response::json([ 'error' => 'Database error' ]);
        }
        return Response::json([ 'success' => 'Event aded to calendar' ]);
    }

    // get signed in users events on calendar
    public function getCalendar()
    {
        $userID = Auth::id();
        // $userID = 1;
        $calendars = Calendar::where('userID', $userID)->get();

        $events = array();
        foreach ($calendars as $key => $calendar)
        {
            $eventID = $calendar->eventID;
            $event = Event::find($eventID);

            if (!empty($event))
            {
                array_push($events, $event);
            }
        }

        if (count($events) == 0)
        {
            return Response::json([ 'error' => 'No events scheduled' ]);
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

        if (empty($calendar))
        {
            return Response::json([ 'error' => 'Event not on calendar' ]);
        }

        if (!$calendar->delete())
        {
            return Response::json([ 'error' => 'Database error' ]);
        }
        return Response::json([ 'success' => 'Event removed form calendar' ]);
    }

    public function attend($eventID, $userID)
    {
        $user = User::find($userID);
        $event = Event::find($eventID);

        $attendEvent = new Calendar;
        $attendEvent->eventID = $eventID;
        $attendEvent->userID = $userID;

        if (!$attendEvent->save())
        {
            return Response::json([ 'error' => 'database error, try again' ]);
        }
    }
    public function Sponsers()
    {
        $sponsers = Sponser::all();
        $sponsersArray = [];
        foreach($sponsers as $sponser)
        {
            array_push($sponsersArray, [
                'label' => $sponser->name,
                'value' => $sponser->name,
                'id' => $sponser->id,
                'logo'=> $sponser->logo,
                'website'=> $sponser->website,
            ]);
        }
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

  public function getTodaysEvents($spaceID) {
    /*$date = new DateTime("now");
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

    foreach($events as $key => $event)
    {
      $event->start = Carbon::createFromTimeStamp(strtotime($event->start))->format('l jS \\of F Y h:i A');
    }

    return Response::json($events);

  }

  public function getDashboardEvents($spaceID)
  {
    $events = Event::where('spaceID', $spaceID)->paginate(30);

    foreach($events as $key => $event)
    {
      $date = Eventdate::where('eventID', $event->id)->first();
      $date->start = Carbon::createFromTimeStamp(strtotime($date->start))->format('l jS \\of F Y');
      $event->date = $date;

      $space = Workspace::find($event->id);
      $event->space = $space;
    }

    return Response::json($events);
  }
}
