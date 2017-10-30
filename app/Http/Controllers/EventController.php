<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Response;
use Purifier;
use Hash;
use Auth;
use JWTAuth;

use App\Event;
use App\User;
use App\Workspace;
use App\Calendar;
use App\Opts;
use App\File;

class EventController extends Controller {

    /** JWTAuth for Routes
     * @param void
     * @return void
     */
    public function __construct() {
        $this->middleware('jwt.auth', ['only' => [
            // 'get',
            // 'store',
            // 'update',
            // 'show',
            // 'search',
            // 'opt',
            // 'getCalendar',
            // 'storeCalendar',
            // 'deleteCalendar',
            // 'delete'
        ]]);
    }

    /** 
     * store()
     * Create event
     * @param void
     * @return  Illuminate\Support\Facades\Response::class
     */
    public function store() {
        $rules = [
            'start' => 'required|string',
            'end' => 'required|string',
            'title' => 'required|string',
            'description' => 'required|string',
            'type' => 'required|string',
            'tags' => 'required|string',
            'local' => 'nullable|string',
            'file' => 'nullable|string'
        ];

        // Validate input against rules
        $validator = Validator::make(Purifier::clean($request->all()), $rules);

        if ( $validator->fails() ) {
            return Response::json(['error' => 'You must fill out all fields.']);
        }

        // user currently signed in
        $userID = Auth::id();
        $spaceID = User::find($userID)->spaceID;
        // required input
        $start = $request->input('start');
        $end = $request->input('end');
        $title = $request->input('title');
        $description = $request->input('description');
        $type = $request->input('type');
        $tags = $request->input('tags');
        // optional input
        $local = $request->input('local');
        $file = $request->input('file');

        // check if another event is in time slot
        $check = $start - $end;
        if ( !$empty(check) ) {
            return Response::json([ 'error' => 'Event already taking place during this time' ]);
        }

        // create ne App\Event
        $event = new Event;
        $event->userID = $userID;
        $event->spaceID = $spaceID;
        $event->start = $start;
        $event->end = $end;
        $event->title = $title;
        $event->description = $description;
        $event->type = $type;
        $event->tags = $tags;

        //optional input
        if (!empty($local)) $event->local = $local;

        if (!$event->save()) {
            return Response::json([ 'error' => 'Database error' ]);  
        }
        // create new App\File;
        if (!empty($file)) {
            $eventID = $event->id;
            $files = explode(',', $file);

            foreach ($files as $key => $file) {
                $file = new File;
                $file->userID = $userID;
                $file->eventID = $eventID;
                // $file->path = TODO;

                if( !$file->save() ) {
                    return Response::json([ 'error' => 'Database error' ]);                                                            
                }
            }
        }
    }


    /**
     * get()
     * get all events from all spaces 
     * @param void
     * @return  Illuminate\Support\Facades\Response::class
     */
    public function get() {
        $events = $Event::all();

        if ( empty($events) ) {
          return Response::json([ 'error' => 'No Events' ]);
        }
        return Response::json([ 'success' => $events ]);
    }

    /**
     * get all events all spaces 
     * @param void
     * @return  Illuminate\Support\Facades\Response::class
     */
    public function update(Request $request) {
        $rules = [
            'eventID', 'required|string',
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

        if ( $validator->fails() ) {
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
        if ( !$empty(check) ) {
            return Response::json([ 'error' => 'Event already taking place during this time' ]);
        }

        // update Event
        $event = Event::find($eventID);
        if ( !empty($start) ) $event->start = $start;
        if ( !empty($end) ) $event->end = $end;
        if ( !empty($title) ) $event->title = $title;
        if ( !empty($description) ) $event->description = $description;
        if ( !empty($type) ) $event->type = $type;
        if ( !empty($tags) ) $event->tags = $tags;

        //optional input
        if ( !empty($local) ) $event->local = $local;

        if ( !$event->save() ) {
                return Response::json([ 'error' => 'Database error' ]);  
        }

        // create new App\File;
        if ( !empty($file) ) {
            $eventID = $event->id;
            $userID = Auth::id();
            $files = explode(',', $file);

            foreach ($files as $key => $file) {
                $file = new File;
                $file->userID = $userID;
                $file->eventID = $eventID;
                // $file->path = TODO;

                if ( !$file->save() ) {
                    return Response::json([ 'error' => 'Database error' ]);
                }
            }   
        }
    }

    /** show()
     * return specific event
     * @param eventID
     * @return event::find(id)
     */
    public function show($eventID) {
        $event = Event::find($eventID);

        if (empty($event)) {
            return Response::json([ 'error' => 'Could not find event' ]);
        }
        return Response::json([ 'success' => $event ]);
    }

    public function search(Request $request) {
        $rules = [
            'query' => 'required|string',
        ];

        // Validate input against rules
        $validator = Validator::make(Purifier::clean($request->all()), $rules);

        if ( $validator->fails() ) {
            return Response::json(['error' => 'You must fill out all fields.']);
        }

        $query = $request->input('query');

        if ( empty($query) ) {
            return Response::json([ 'error' => 'No search query recieved' ]);
        }

        $search = Event::
        where('title', 'LIKE', $query)
        ->Orwhere('tags', 'LIKE', $query)
        ->Orwhere('type', 'LIKE', $query)
        ->Orwhere('description', 'LIKE', $query)
        ->get();

        if ( !empty($search) ) {
            return Response::json([ 'success' => $search ]);
        }
        return Response::json([ 'error' => 'Nothing matched your query' ]);
    }

    // allow workspaces to opt-in to a remote event at another workspace
    public function opt(Request $request) {
        $rules = [
            'spaceID' => 'required|string',
            'eventID' => 'required|string'
        ];

        // Validate input against rules
        $validator = Validator::make(Purifier::clean($request->all()), $rules);

        if ( $validator->fails() ) {
            return Response::json(['error' => 'You must fill out all fields.']);
        }

        // form input
        $spaceID = $request->input('spaceID');
        $eventID = $request->input('eventID');

        $opt = new Opt;
        $opt->spaceID = $spaceID;
        $opt->eventID = $eventID;

        if ( !$opt->save() ) {
            return Response::json([ 'error' => 'Database error' ]);
        }
        return Response::json([ 'success' => 'Joined event!' ]);
    }

    // delete event  
    public function delete($eventID) {
        // find event
        $event = Event::find($eventID);
        // check if exists
        if ( empty($event) ) {
            return Response::json([ 'error' => 'No event with '.$eventID ]);
        } 
        else {
            // delete event
            $event->delete();

            // delete callendars associated with event
            $calendars = Calendar::where('eventID', $eventID)->get();
            if (!empty($calendars)) {
                foreach ($calendars as $key => $calendar) {
                    $calendar->delete();
                }
            }

            // delete opts associated with event
            $opts = Opt::where('eventID', $eventID)->get();

            if (!empty($opts)) {
                foreach ($opts as $key => $opt) {
                    $opt->delete();
                }
            }

            // delete files associated with event
            $files = File::where('eventID', $eventID);

            if ( !empty($files) ) {
                foreach ($files as $key => $file) {
                    $file->delete();
                }
            }
        }
    }

    // user add event to calendar
    public function storeCalendar($eventID) {
        $userID = Auth::id();

        $calendar = new Calendar;
        $calendar->userID = $userID;
        $calendar->eventID = $eventID;

        if ( !$calendar->save() ) {
            return Response::json([ 'error' => 'Database error' ]);
        }

        return Response::json([ 'success' => 'Event aded to calendar' ]);
    }

    // get signed in users events on calendar 
    public function getCalendar() {
        $userID = Auth::id();
        $calendars = Calendar::where('userID', $userID)->get();

        $events = array();

        foreach ($calendars as $key => $calendar) {
            $eventID = $calendar->eventID;
            $event = Event::find($eventID);

            if( !empty($event) ) {
                array_push($events, $event);
            }
        }

        if ( count($events) == 0 ) {
        return Response::json([ 'error' => 'No events scheduled' ]);
        }
        return Response::json([ 'success' => $events ]);
    }

    // delete event from signed in users calendar
    public function deleteCalendar($eventID) {
        $userID = Auth::id();

        $calendar = Calendar::
        where('userID', $userID)
        ->orWhere('eventID', $eventID)
        ->first();

        if ( empty($calendar) ) {
            return Response::json([ 'error' => 'Event not on calendar' ]);
        }

        if ( !$calendar->delete() ) {
            return Response::json([ 'error' => 'Database error' ]);
        }
        return Response::json([ 'success' => 'Event removed form calendar' ]);
    }

}