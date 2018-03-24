<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Purifier;
use Response;
use Auth;
use Image;

use App\Category;
use App\Challenge;
use App\Cbind;
use App\Ptbind;
use App\Upload;
use App\Submission;
//use App\Team;
use App\User;
use App\Workspace;
use App\Eventdate;

use Carbon\Carbon;


class ChallengesController extends Controller
{
  public function __construct()
  {
    $this->middleware('jwt.auth', ['only' => ['store', 'joinChallenge', 'uploadFile', 'storeSubmission', 'deleteSubmission']]);
  }

  public function index($count)
  {
    $challenges = Challenge::where('challenges.status', 'Approved')->join('workspaces', 'challenges.spaceID', '=', 'workspaces.id')
    ->select(
      'challenges.id',
      'challenges.challengeImage',
      'challenges.challengeTitle',
      'challenges.challengeContent',
      'challenges.challengeSlug',
      'challenges.spaceID',
      'challenges.startDate',
      'challenges.endDate',
      'workspaces.logo',
      'workspaces.name',
      'workspaces.city'
    )
    ->orderBy('challenges.created_at', 'DESC')
    ->paginate($count);

    foreach($challenges as $key => $challenge)
    {
      $categories = Cbind::where('cbinds.challengeID', $challenge->id)->join('categories', 'cbinds.categoryID', '=', 'categories.id')
        ->select(
          'categories.id',
          'categories.categorySlug',
          'categories.categoryName',
          'categories.categoryColor',
          'categories.categoryTextColor'
        )
        ->get();

      $challenge->categories = $categories;
      if(strlen($challenge->challengeContent) > 200) {
        $challengeContent = substr(strip_tags($challenge->challengeContent), 0, 200);
        $challengeContent = $challengeContent.'...';
        $challenge->challengeContent = $challengeContent;      
      }
    }

    return Response::json(['challenges' => $challenges]);
  }

  public function upcoming($count)
  {
    $challenges = Challenge::whereDate('challenges.startDate', '>', date('Y-m-d'))->where('challenges.status', 'Approved')->join('workspaces', 'challenges.spaceID', '=', 'workspaces.id')
    ->select(
      'challenges.id',
      'challenges.challengeImage',
      'challenges.challengeTitle',
      'challenges.challengeContent',
      'challenges.challengeSlug',
      'challenges.spaceID',
      'challenges.startDate',
      'challenges.endDate',
      'workspaces.logo',
      'workspaces.name',
      'workspaces.city'
    )
    ->orderBy('created_at', 'DESC')
    ->paginate($count);

    foreach($challenges as $key => $challenge)
    {
      $categories = Cbind::where('cbinds.challengeID', $challenge->id)->join('categories', 'cbinds.categoryID', '=', 'categories.id')
        ->select(
          'categories.id',
          'categories.categorySlug',
          'categories.categoryName',
          'categories.categoryColor',
          'categories.categoryTextColor'
        )
        ->get();

      $challenge->categories = $categories;
      $challenge->challengeContent = substr(strip_tags($challenge->challengeContent), 0, 200);

    }

    return Response::json(['challenges' => $challenges]);
  }

  public function store(Request $request)
  {
    $rules = [
      'challengeImage' => 'required',
      'challengeTitle' => 'required',
      'challengeContent' => 'required',
    ];

    $validator = Validator::make(Purifier::clean($request->all()), $rules);
    if($validator->fails())
    {
      return Response::json(['error' => 'Please fill out all fields.']);
    }

    $user = Auth::user();

    $spaceID = $user->spaceID;
    $challengeImage = $request->file('challengeImage');
    $challengeTitle = $request->input('challengeTitle');
    $challengeContent = $request->input('challengeContent');
    $eventID = $request->input('eventID');
    //$challengeCategories = json_decode($request->input('challengeCategories'));
    $status = 'Approved';

    $challengeSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $challengeTitle)));
    $slugCheck = Challenge::where('challengeSlug', $challengeSlug)->first();
    if(!empty($slugCheck))
    {
      $str = str_random(4);
      $challengeSlug = $challengeSlug.'-'.$str;
    }

    $imageFile = 'challenge';
    if (!is_dir($imageFile)) {
      mkdir($imageFile,0777,true);
    }

    $imageName = str_random(4);
    if($challengeImage->getClientSize() > 5242880)
    {
      return Response::json(['error' => 'This image is too large.']);
    }
    if($challengeImage->getClientMimeType() != "image/png" && $challengeImage->getClientMimeType() != "image/jpeg" && $challengeImage->getClientMimeType() != "image/gif")
    {
      return Response::json(['error' => 'Not a valid PNG/JPG/GIF image.']);
    }
    $ext = $challengeImage->getClientOriginalExtension();
    $challengeImage->move($imageFile, $imageName.'.'.$ext);
    $challengeImage = $imageFile.'/'.$imageName.'.'.$ext;
    $img = Image::make($challengeImage);
    list($width, $height) = getimagesize($challengeImage);
    if($width > 512)
    {
      $img->resize(512, null, function ($constraint) {
          $constraint->aspectRatio();
      });
      if($height > 512)
      {
        $img->crop(512, 512);
      }
    }
    $img->save($challengeImage);

    $challenge = new Challenge;
    $challenge->spaceID = $spaceID;
    $challenge->eventID = $eventID;
    $challenge->challengeImage = $request->root().'/'.$challengeImage;
    $challenge->challengeTitle = $challengeTitle;
    $challenge->challengeSlug = $challengeSlug;
    $challenge->challengeContent = $challengeContent;
    $challenge->status = $status;
    $challenge->save();

    /*if(!count($challengeCategories) < 0) {
      foreach($challengeCategories as $key => $category)
      {
        $cbind = new Cbind;
        $cbind->challengeID = $challenge->id;
        $cbind->categoryID = $category->value;
        $cbind->save();
      }
    }*/

    return Response::json(['challenge' => $challenge->challengeSlug]);
  }

  public function update(Request $request, $id)
  {
    $rules = [
      'challengeTitle' => 'required',
      'challengeContent' => 'required',
    ];

    $validator = Validator::make(Purifier::clean($request->all()), $rules);
    if($validator->fails())
    {
      return Response::json(['error' => 'Please fill out all fields.']);
    }

    $user = Auth::user();

    if($request->hasFile('challengeImage'))
    {
      $challengeImage = $request->file('challengeImage');
      $imageFile = 'challenge';
      if (!is_dir($imageFile)) {
        mkdir($imageFile,0777,true);
      }

      $imageName = str_random(4);
      if($challengeImage->getClientSize() > 5242880)
      {
        return Response::json(['error' => 'This image is too large.']);
      }
      if($challengeImage->getClientMimeType() != "image/png" && $challengeImage->getClientMimeType() != "image/jpeg" && $challengeImage->getClientMimeType() != "image/gif")
      {
        return Response::json(['error' => 'Not a valid PNG/JPG/GIF image.']);
      }
      $ext = $challengeImage->getClientOriginalExtension();
      $challengeImage->move($imageFile, $imageName.'.'.$ext);
      $challengeImage = $imageFile.'/'.$imageName.'.'.$ext;
      $img = Image::make($challengeImage);
      list($width, $height) = getimagesize($challengeImage);
      if($width > 512)
      {
        $img->resize(512, null, function ($constraint) {
            $constraint->aspectRatio();
        });
        if($height > 512)
        {
          $img->crop(512, 512);
        }
      }
      $img->save($challengeImage);
      $challenge->challengeImage = $request->root().'/'.$challengeImage;
    }

    $challengeTitle = $request->input('challengeTitle');
    $challengeContent = $request->input('challengeContent');
    //$challengeCategories = json_decode($request->input('challengeCategories'));
    $status = 'Approved';

    /*$challengeSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $challengeTitle)));
    $slugCheck = Challenge::where('challengeSlug', $challengeSlug)->first();
    if(!empty($slugCheck))
    {
      $str = str_random(4);
      $challengeSlug = $challengeSlug.'-'.$str;
    }*/

    $challenge = Challenge::find($id);
    $challenge->challengeTitle = $challengeTitle;
    //$challenge->challengeSlug = $challengeSlug;
    $challenge->challengeContent = $challengeContent;
    $challenge->status = $status;
    $challenge->save();

    /*foreach($challengeCategories as $key => $category)
    {
      $categoryCheck = Cbind::where('challengeID', $challenge->id)->where('categoryID', $category->value)->first();
      if(empty($categoryCheck))
      {
        $cbind = new Cbind;
        $cbind->challengeID = $challenge->id;
        $cbind->categoryID = $category->value;
        $cbind->save();
      }
    }*/

    return Response::json(['challenge' => $challenge->id]);
  }

  public function uploadFile(Request $request)
  {
    $rules = [
      'challengeID' => 'required',
      'challengeFile' => 'required',
    ];

    $validator = Validator::make(Purifier::clean($request->all()), $rules);
    if($validator->fails())
    {
      return Response::json(['error' => 'Please fill out all fields.']);
    }

    $challengeID = $request->input('challengeID');
    $challengeFile = $request->file('challengeFile');

    $user = Auth::user();
    $challenge = Challenge::find($challengeID);

    if($challenge->spaceID != $user->spaceID) {
      return Response::json(['error' => 'You do not have permission to do this.']);
    }

    $challengeSlug = $challenge->challengeSlug;

    if (!is_dir("uploads/".$challengeSlug)) {
      mkdir("uploads/".$challengeSlug,0777,true);
    }

    $fileName = $challengeFile->getClientOriginalName();
    $challengeFile->move("uploads/".$challengeSlug, $fileName);

    $upload = new Upload;
    $upload->challengeID = $challenge->id;
    $upload->fileData = $request->root()."/uploads/".$challengeSlug.'/'.$fileName;
    $upload->fileName = $fileName;
    $upload->save();

    return Response::json(['success' => 'File Uploaded.']);
  }

  public function show($id)
  {
    $challenge = Challenge::where('challenges.challengeSlug', $id)->where('challenges.status', 'Approved')->join('workspaces', 'challenges.spaceID', '=', 'workspaces.id')->join('events', 'challenges.eventID', '=', 'events.id')
    ->select(
      'challenges.id',
      'challenges.challengeImage',
      'challenges.challengeTitle',
      'challenges.challengeContent',
      'challenges.challengeSlug',
      'challenges.spaceID',
      'challenges.eventID',
      'workspaces.logo',
      'workspaces.name',
      'workspaces.city',
      'events.state',
      'events.city',
      'events.address',
      'events.zipcode'
    )
    ->first();

    $eventDates = Eventdate::where('eventID', $challenge->eventID)->get();
    foreach ($eventDates as $key => $date) {
      $date->startFormatted = Carbon::createFromTimeStamp(strtotime($date->start))->format('l jS \\of F Y h:i A');
      $date->endFormatted = Carbon::createFromTimeStamp(strtotime($date->end))->format('l jS \\of F Y h:i A');
    }
    $workspace = Workspace::where('id', $challenge->spaceID)->first();

    $categories = Cbind::where('cbinds.challengeID', $challenge->id)->join('categories', 'cbinds.categoryID', '=', 'categories.id')
      ->select(
        'categories.id',
        'categories.categorySlug',
        'categories.categoryName',
        'categories.categoryColor',
        'categories.categoryTextColor'
      )
      ->get();

    $challenge->categories = $categories;

    $categoriesArray = [];
    foreach($challenge->categories as $key => $c)
    {
      $categoriesArray[$key]['value'] = $c->id;
      $categoriesArray[$key]['label'] = $c->categoryName;
    }

    $uploads = Upload::where('challengeID', $challenge->id)->get();

    $teams = Ptbind::where('ptbinds.challengeID', $challenge->id)->join('users', 'ptbinds.userID', '=', 'users.id')->select('users.id', 'users.avatar', 'users.name')->inRandomOrder()->take(10)->get();

    return Response::json(['challenge' => $challenge, 'uploads' => $uploads, 'teams' => $teams, 'categoriesArray' => $categoriesArray, 'eventDates' => $eventDates, 'workspace' => $workspace]);
  }

  public function showTeams($id)
  {
    $teams = Ptbind::where('ptbinds.challengeID', $id)->join('users', 'ptbinds.userID', '=', 'users.id')
      ->select(
      'users.avatar',
      'users.name'
      )
      ->paginate(15);

    return Response::json(['teams' => $teams]);
  }

  public function joinChallenge($id)
  {
    $user = Auth::user();
    /*$profile = Profile::where('userID', $user->id)->first();
    $team = Team::where('profileID', $profile->id)->first();
    if(empty($team))
    {
      return Response::json(['error' => 'You are not a Team Leader.']);
    }
    */

    $bindCheck = Ptbind::where('userID', $user->id)->where('challengeID', $id)->first();
    if(!empty($bindCheck))
    {
      return Response::json(['error' => 'You are already part of this Challenge.']);
    }

    $ptbind = new Ptbind;
    $ptbind->userID = $user->id;
    $ptbind->challengeID = $id;
    $ptbind->save();

    return Response::json(['success' => 'Challenge Joined!']);
  }

  /*public function updateStatus($id, $type)
  {
    $user = Auth::user();
    $profile = Profile::where('userID', $user->id)->first();

    $challenge = Challenge::where('profileID', $profile->id)->where('id', $id)->first();

    if($type == 'Open') {
      $challenge->status = 'Open';
    }
    else if($type == 'Close') {
      $challenge->status = 'Closed';
    }

    return Response::json(['success' => 'Challenge Updated.']);
  }*/

  /*public function setWinner($id, $uid)
  {
    $user = Auth::user();
    $profile = Profile::where('userID', $user->id)->first();

    $challenge = Challenge::where('profileID', $profile->id)->where('id', $id)->first();

  }
  */

  /*public function getPending()
  {
    $user = Auth::user();
    $profile = Profile::where('userID', $user->id)->first();
    if($profile->roleID != 1)
    {
      return Response::json(['error' => 'You do not have permission.']);
    }

    $challenges = Challenge::whereDate('challenges.startDate', '<=', date('Y-m-d'))->where('challenges.status', 'Pending')->join('profiles', 'challenges.profileID', '=', 'profiles.id')
    ->select(
      'challenges.id',
      'challenges.challengeImage',
      'challenges.challengeTitle',
      'challenges.challengeContent',
      'challenges.challengeSlug',
      'challenges.profileID',
      'challenges.startDate',
      'challenges.endDate',
      'profiles.avatar',
      'profiles.profileName',
      'profiles.profileTitle'
    )
    ->orderBy('created_at', 'DESC')
    ->paginate($count);

    foreach($challenges as $key => $challenge)
    {
      $categories = Cbind::where('cbinds.challengeID', $challenge->id)->join('categories', 'cbinds.categoryID', '=', 'categories.id')
        ->select(
          'categories.id',
          'categories.categorySlug',
          'categories.categoryName',
          'categories.categoryColor',
          'categories.categoryTextColor'
        )
        ->get();

      $challenge->categories = $categories;
    }

    return Response::json(['challenges' => $challenges]);
  }*/
  /*
  public function approve($id)
  {
    $user = Auth::user();
    $profile = Profile::where('userID', $user->id)->first();
    if($profile->roleID != 1)
    {
      return Response::json(['error' => 'You do not have permission.']);
    }

    $challenge::find($id);
    $challenge->status = "Approved";

    return Response::json(['success' => 'Challenge Approved.']);
  }
*/
  public function search(Request $request)
  {
    $rules = [
      'searchContent' => 'required'
    ];

    $validator = Validator::make(Purifier::clean($request->all()), $rules);
    if($validator->fails())
    {
      return Response::json(['error' => 'Please fill out all fields.']);
    }

    $searchContent = $request->input('searchContent');

    $challenges = Challenge::where('challenges.status', 'Approved')
    ->where('challenges.challengeTitle', 'LIKE', '%'.$searchContent.'%')
    ->orWhere('challenges.challengeContent', 'LIKE', '%'.$searchContent.'%')
    ->join('workspaces', 'challenges.spaceID', '=', 'workspaces.id')
    ->select(
      'challenges.id',
      'challenges.challengeImage',
      'challenges.challengeTitle',
      'challenges.challengeContent',
      'challenges.challengeSlug',
      'challenges.spaceID',
      'challenges.startDate',
      'challenges.endDate',
      'workspaces.logo',
      'workspaces.name',
      'workspaces.city'
    )
    ->orderBy('challenges.created_at', 'DESC')
    ->get();

    foreach($challenges as $key => $challenge)
    {
      $categories = Cbind::where('cbinds.challengeID', $challenge->id)->join('categories', 'cbinds.categoryID', '=', 'categories.id')
        ->select(
          'categories.id',
          'categories.categorySlug',
          'categories.categoryName',
          'categories.categoryColor',
          'categories.categoryTextColor'
        )
        ->get();

      $challenge->categories = $categories;
      $challenge->challengeContent = substr(strip_tags($challenge->challengeContent), 0, 200);

    }

    return Response::json(['challenges' => $challenges]);

  }

  public function getSubmissions($id)
  {
    $submissions = Submission::where('submissions.challengeID', $id)->join('users', 'submissions.userID', '=', 'users.id')->select('submissions.id', 'users.name', 'users.avatar', 'submissions.submissionTitle', 'submissions.submissionDescription', 'submissions.submissionGithub', 'submissions.submissionVideo', 'submissions.submissionFile')->get();

    return Response::json(['submissions' => $submissions]);
  }

  public function storeSubmission(Request $request)
  {
    $rules = [
      'submissionTitle' => 'required',
      'submissionDescription' => 'required',
      'submissionFile' => 'required',
      'challengeID' => 'required'
    ];

    $validator = Validator::make(Purifier::clean($request->all()), $rules);
    if($validator->fails())
    {
      return Response::json(['error' => 'Please fill out all fields.']);
    }

    $user = Auth::user();
    $challengeID = $request->input('challengeID');

    $challengeCheck = Submission::where('userID', $user->id)->where('challengeID', $challengeID)->first();
    if(!empty($challengeCheck)) 
    {
      return Response::json(['error' => 'You have already submitted a solution']);
    }

    $submissionTitle = $request->input('submissionTitle');
    $submissionDescription = $request->input('submissionDescription');
    $submissionGithub = $request->input('submissionGithub');
    $submissionVideo = $request->input('submissionVideo');
    $submissionFile = $request->file('submissionFile');

    $extension = $submissionFile->getClientOriginalExtension();
    if($extension != 'zip')
    {
      return Response::json(['error' => 'This is not a valid ZIP.']);
    }

    $size = $submissionFile->getClientSize();
    if($size > 8388608)
    {
      return Response::json(['error' => 'This ZIP is too large.']);
    }

    if (!is_dir("submissions/".$challengeID)) {
      mkdir("submissions/".$challengeID,0777,true);
    }

    $fileName = $submissionFile->getClientOriginalName();
    $submissionFile->move("submissions/".$challengeID, $fileName);

    $submission = new Submission;
    $submission->userID = $user->id;
    $submission->challengeID = $challengeID;
    $submission->submissionTitle = $submissionTitle;
    $submission->submissionDescription = $submissionDescription;
    $submission->submissionGithub = $submissionGithub;
    $submission->submissionVideo = $submissionVideo;
    $submission->submissionFile = $request->root()."/submissions/".$challengeID.'/'.$fileName;
    $submission->save();

    return Response::json(['success' => 'Solution Submitted']);

  }

  public function deleteSubmission($id)
  {
    $user = Auth::user();
    $submission = Submission::find($id);

    if($user->roleID == 2 || $submission->userID == $user->id)
    {
      $submission->delete();

      return Response::json(['success' => 'Submission Deleted']);
    }
    else {
      return Response::json(['error' => 'You do not have permission.']);
    }
  }
}
