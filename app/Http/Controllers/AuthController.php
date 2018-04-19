<?php
namespace App\Http\Controllers;

use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Mews\Purifier\Facades\Purifier;
use Illuminate\Support\Facades\Response;
use App\User;
use App\Workspace;
use App\Event;
use App\Eventdate;
use App\Calendar;
use App\Services\InputValidator;
use App\Services\Stripe\SubscriptionService;

class AuthController extends Controller
{
  private $inputValidator;
  public function __construct(InputValidator $inputValidator)
  {
    $this->inputValidator = $inputValidator;
    $this->middleware('jwt.auth', ['only' => [
      'getUsers',
      'ban',
      'checkAuth',
      'getUser',
      'allCustomers',
    ]]);
  }

  private function recaptcha($response)
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
    curl_setopt(
      $ch,
      CURLOPT_POSTFIELDS,
      "secret=" . config('services.recaptcha.secret') . "&response=" . $response
    );
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $recaptcha_response = curl_exec($ch);
    curl_close($ch);
    return $recaptcha_response;
  }
  public function checkAuth()
  {
    $user = Auth::user();
    return Response::json(['user' => $user]);
  }

  public function allCustomers()
  {
    $user = Auth::user();
    $spaceID = $user->spaceID;
    $space = Workspace::find($spaceID)->makeVisible('stripe');
    if ($space->stripe != null) {
      $subscriptionService = new SubscriptionService($space->stripe);
      $customers = $subscriptionService->getAllCustomers();
      return Response::json(['customers' => $customers]);
    } else {
      return Response::json(['error' => true]);
    }
  }

  /** SIGN UP
   * Persist user to database after sign up.
   * @param Illuminate\Support\Facades\Request::class
   * @return  Illuminate\Support\Facades\Response::class
   */
  public function signUp(Request $request, $roleId = null, $spaceID = null)
  {
    $returnAsHttpResponse = (($roleId == null) && ($spaceID == null));

    $validInput = array_key_exists('avatar', $_FILES)
      ? $this->inputValidator->validateSignUp($request, $spaceID, $_FILES['avatar'])
      : $this->inputValidator->validateSignUp($request, $spaceID);

    if (!$validInput['isValid']) {
      if ($returnAsHttpResponse) {
        return Response::json(['error' => $validInput['message']]);
      } else {
        return [
          'hasErrors' => true,
          'message' => $validInput['message']
        ];
      }
    }
    $recaptcha_response = json_decode($this->recaptcha($request['recaptcha']));
    $success = $recaptcha_response->success;
    if (!$success) {
      if ($returnAsHttpResponse) {
        return Response::json(['error' => 'You a bot!']);
      } else {
        return [
          'hasErrors' => true,
          'message' => 'You a bot!'
        ];
      }
    }
    $plan = $request['plan'];
    $userDidNotChooseFreeTier = (($plan != "free") && !empty($plan));

    $avatar = $request->file('avatar');

        // Create new App\User;
    $user = $returnAsHttpResponse
      ? new User($request->except(['password', 'avatar', 'recaptcha']))
      : new User($request->except(['email', 'name', 'password', 'avatar', 'useremail', 'username', 'recaptcha']));
        // Required input
    $user->spaceID = $spaceID != null ? $spaceID : $request['spaceID'];
    $user->roleID = $roleId != null ? $roleId : 3;
    if ($request['useremail']) $user->email = $request['useremail'];
    if ($request['username']) $user->name = $request['username'];
    $user->password = Hash::make($request['password']);

        // Profile Picture
    if (!empty($avatar)) {
      $avatarName = $avatar->getClientOriginalName();
      $avatar->move('storage/avatar/', $avatarName);
      $avatar = $request->root() . '/storage/avatar/' . $avatarName;
    } else {
      $sub = substr($request['name'], 0, 2);
      $avatar = "https://invatar0.appspot.com/svg/" . $sub . ".jpg?s=100";
    }

    $user->avatar = $avatar;

    $plan = $request['plan'];
    $user->subscriber = $userDidNotChooseFreeTier ? 1 : 0;

    if ($userDidNotChooseFreeTier) DB::beginTransaction();

    if (!$user->save()) {
      if ($returnAsHttpResponse) {
        DB::rollBack();
        return Response::json(['error' => 'Account not created: Please try again']);
      } else {
        return [
          'hasErrors' => true,
          'message' => 'account not created: please try again'
        ];
      }
    }

    if ($userDidNotChooseFreeTier) {
      $space = Workspace::find(($spaceID != null) ? $spaceID : $request['spaceID'])->makeVisible('stripe');
      $customerData = [
        "cardToken" => $request['customerToken'],
        "customer_idempotency_key" => $request['customer_idempotency_key'],
        "subscription_idempotency_key" => $request['subscription_idempotency_key'],
        "email" => $request['email'],
        "plan" => $plan,
        "userID" => $user->id
      ];

      $subscriptionService = new SubscriptionService($space->stripe);
      $customerCreatedAndCharged = $subscriptionService->createCustomer($customerData);
      if (!$customerCreatedAndCharged) {
        DB::rollback();
        return Response::json(['refresh_idempotency_key' => true]);
      }
    } 

        // Mail::send('emails.signUp', array(),
        // function($message) use ($name, $email)
        // {
        //   $message->from('heythere@innovationmesh.com', 'Innovation Mesh');
        //   $message->to($email)->subject('Thanks for Joining!');
        // });

    $email = $request['email'];
    $password = $request['password'];
    $credentials = compact("email", "password");
    $token = JWTAuth::attempt($credentials);
    if ($returnAsHttpResponse) {
      DB::commit();
      return Response::json(['user' => [
        'id' => $user->id,
        'roleID' => $user->roleID,
        'token' => $token
      ]]);
    } else {
      return [
        'hasErrors' => false
      ];
    }
  }


  /**
   * Sign In
   *
   * @param Illuminate\Support\Facades\Request::class
   * @return  Illuminate\Support\Facades\Response::class
   */
  public function signIn(Request $request)
  {
        // required input
    $rules = [
      'email' => 'required',
      'password' => 'required'
    ];

        // Validate and purify input
    $validator = Validator::make(Purifier::clean($request->all()), $rules);

    if ($validator->fails()) {
      return Response::json(['error' => 'Please fill out all fields.']);
    }

    $password = $request->input('password');
        // get user input
    $email = $request->input('email');
    $password = $request->input('password');

    $user = User::where('email', $email)->first();

        // generate token
    $credentials = compact("email", "password");
    $token = JWTAuth::attempt($credentials);

    if ($token == false) {
      return Response::json(['error' => 'Wrong Email/Password']);
    }

    return Response::json([
      'token' => $token,
    ]);
  }

  private function getUpcomingEvents()
  {
    $now = new DateTime();
    $eventdates = Eventdate::where('start', '>', $now->format('Y-m-d'))->get();

    $eventIDs = array();
    foreach ($eventdates as $key => $event) {
      if ($key == 0) {
        $id = $event->eventID;
        array_push($eventIDs, $id);
      }
      if ($key != 0) {
        $check = $event->eventID;
        if ($id != $check) {
          $id = $check;
          array_push($eventIDs, $id);
        }
      }
    }
    $events = array();
    foreach ($eventIDs as $id) {
      $event = Event::find($id);
      array_push($events, $event);
    }
    return $events;

  }

  private function getAttendingEvents($userID)
  {
    $now = new DateTime();
    $attending = Calendar::where('userID', $userID)->get();
    $upcoming = array();
    if (!empty($attending)) {
      foreach ($attending as $attend) {
        $eventdate = Eventdate::where('eventID', $attend->eventID)->first();
        if (!empty($eventdate)) {
          $event = Event::find($attend->eventID);
          $title = $event->title;
          $id = $event->id;
          $eDate = new DateTime($eventdate->start);
          $diff = $now->diff($eDate);
          $formattedDiff = $diff->format('%R%a');

          if ((int)$formattedDiff > 0) {
            array_push(
              $upcoming,
              [
                "title" => $title,
                "id" => $id
              ]
            );
          }
        }
      }
    }
    return $upcoming;
  }

  /**
   * Get users
   * @param spaceID
   * @return  Illuminate\Support\Facades\Response::class
   **/
  public function getUsers()
  {
    $organizer = Auth::user();
    if ($organizer->roleID != 2) {
      return Response::json(['error' => 'invalid role']);
    }

    $users = User::where('spaceID', $organizer->spaceID)->get();

    if (!empty($users)) {
      return Response::json(['users' => $users]);
    } else {
      return Response::json(['error' => 'no users for space']);
    }
  }

  /**
   * Ban User
   * @param userID
   * @return  Illuminate\Support\Facades\Response::class
   **/
  public function ban($id)
  {
    $admin = Auth::user();


    if ($admin->roleID != 1) {
      return Response::json(['error' => 'invalid credintials']);
    }
    $bannedUser = User::where('id', $id)->first();
    $bannedUser->ban = 1;

    if (!$bannedUser->save()) {
      return Response::json(['error' => 'datebase error']);
    }
    return Response::json(['success' => 'user' . $bannedUser->name . ' has been banned from MeshNetwork']);
  }

  public function getUser()
  {
    $auth = Auth::user();
    $user = User::find($auth->id);

    $skills = $user->skills;
    if ($skills == null || strlen($skills) == 0 || $skills == "") {
      $skills = [];
    } else {
      $skills = explode(",", $skills);
    }

    return Response::json(['user' => $user, 'skills' => $skills]);
  }

  public function resetPassword(Request $request)
  {
    $rules = [
      'email' => 'required'
    ];

    $validator = Validator::make(Purifier::clean($request->all()), $rules);

    if ($validator->fails()) {
      return Response::json(['error' => 'You must fill out all fields.']);
    }

    $email = $request->input('email');
    $user = User::where('email', '=', $email)->first();
    $password = str_random(6);
    $hashword = Hash::make($password);

    $user->password = $hashword;
    $user->save();

    $name = $user->name;

    Mail::send(
      'emails.passwordReset',
      array('name' => $name, 'email' => $email, 'password' => $password),
      function ($message) use ($name, $email, $password) {
        $message->from('heythere@innovationmesh.com', 'Innovation Mesh');
        $message->to($email)->subject('Password Reset');
      }
    );

    return Response::json(['success' => 'Check your E-mail for your Temp password.']);

  }

}
