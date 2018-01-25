<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
// /*
// |--------------------------------------------------------------------------
// | API Routes
// |--------------------------------------------------------------------------
// |
// | Here is where you can register API routes for your application. These
// | routes are loaded by the RouteServiceProvider within a group which
// | is assigned the "api" middleware group. Enjoy building your API!
// |
// */
// DashboardController

// WORKS

// SpaceController
Route::resource('workspaces', 'SpaceController');
// PhotoController
Route::resource('photos', 'PhotoController');
// DashboardController
Route::get('joins/{slug}', 'DashboardController@Joins');
Route::get('appearances/{slug}', 'DashboardController@Appearances');
Route::get('appearances/range/{spaceId}/{startMonth}/{startYear}/{endMonth}/{endYear}', 'DashboardController@appearanceForMonthYear');

// AuthController
Route::get('authorize', 'AuthController@checkAuth');  // sign up
Route::post('signUp', 'AuthController@signUp');  // sign up
Route::post('login', 'AuthController@signIn');  // login

// UserController
Route::get('user/auth', 'AuthController@getUser'); // get Auth User
Route::get('user/profile/{id}', 'UserController@user'); // get user.id
Route::post('user/update', 'UserController@updateUser'); // logged in user profile update

// TESTING
Route::get('userskills', 'UserController@userSkills'); // to populate tags in sign up form
Route::get('skills', 'UserController@getSkills'); // to populate tags in sign up form
Route::get('skills/all', 'UserController@allSkills'); // to populate tags in sign up form
Route::post('search', 'UserController@search'); // search by skill/SpaceID
Route::get('organizers/events', 'UserController@OrganizersForEvents'); // to populate tags in sign up form
Route::get('users/{spaceID}', 'UserController@getSpaceUsers');
Route::get('users/space/{spaceID}', 'UserController@usersFromSpace'); // get users from spaceID (for Kiosk)
Route::get('workspace/{slugOrSpaceID}', 'WorkspaceController@show');
Route::get('events/space/{spaceID}', 'WorkspaceController@events');
Route::get('plans/{slug}', 'WorkspaceController@getSubscriptions');
Route::get('publickey/{slug}', 'WorkspaceController@getKey');
Route::get('organizers/space/{spaceID}', 'WorkspaceController@spaceOrganizers');
Route::get('/space/metrics/{spaceID}', 'WorkspaceController@getSpaceStats');
Route::get('sponsors', 'EventController@Sponsers');
Route::post('event', 'EventController@store');
Route::get('event/{eventID}', 'EventController@show');
Route::get('todayevent/{spaceID}', 'EventController@getTodaysEvents');
Route::get('events/{spaceID}', 'EventController@getDashboardEvents');
Route::post('appearance', 'AppearanceController@store');
Route::get('resources/{spaceID}', 'BookingController@getResources');
Route::post('resource', 'BookingController@storeResource');
Route::post('resource/delete/{id}', 'BookingController@deleteResource');
Route::get('occasions', 'AppearanceController@getValidOccasions');
Route::get('bookings/{resourceID}', 'BookingController@getBookings');
Route::post('booking', 'BookingController@store');
// Route::get('log/{message}', 'DashboardController@log');
// Route::get('email', 'DashboardController@email');


// AuthController
// Route::get('users', 'AuthController@getUsers');  // admin get users
// Route::get('ban/{id}', 'AuthController@ban'); // ban user.id

// UserController
// Route::get('deleteuser/{id}', 'UserController@delete'); // Admin delete user
// Route::post('searchname', 'UserController@searchName'); // search by name/spaceID
// Route::get('showuser', 'UserController@showUser'); // show logged in user
// Route::get('organizers/all', 'UserController@Organizers'); // to populate tags in sign up form
// Route::get('userToOrg/{userID}', 'UserController@makeOrganizer');

// // RoleController
// Route::post('newrole', 'RoleController@store');
// Route::get('getroles', 'RoleController@get');
// Route::post('showrole', 'RoleController@show');
// Route::get('deleterole/{id}', 'RoleController@delete');

// // WorkspaceController
// Route::get('spacestatus/{spaceID}/{status}', 'WorkspaceController@approve');
//Route::patch('workspace', 'WorkspaceController@update');
//Route::resource('workspace', 'WorkspaceController', ['names' => [
//    'update' => 'workspace'
//]]);
// Route::get('getSpaceBySlug/{slug}', 'WorkspaceController@getSpaceBySlug');

// // EventController
// Route::post('sponser','EventController@makeSponser');
// Route::get('events','EventController@get');
/* Route::get('upcoming/{spaceID}','EventController@upcoming'); */
// Route::post('eventUpdate','EventController@update');
// Route::post('searchEvent','EventController@search');
// Route::post('optEvent, ','EventController@opt');
// Route::get('deleteEvent/{id}','EventController@delete');
// Route::get('getCalendar','EventController@getCalendar');
// Route::get('event/join/{eventID}','EventController@storeCalendar');
// Route::get('deleteCalendar/{id}','EventController@deleteCalendar');
// Route::get('eventOrganizers/{id}', 'EventController@EventOrganizers');
// Route::get('eventDates/{id}', 'EventController@EventDates');
// Route::get('today/event', 'EventController@getTodaysEvents');

// // AppeanceController
// Route::get('appearances','AppearanceController@get');
// Route::get('countAppearances/{sort}/{eventID?}/{spaceID?}','AppearanceController@getCount');
// Route::get('appearance/{userID}','AppearanceController@show');
// Route::get('storeInvite','AppearanceController@stroreInvite');
// Route::get('getInvite','AppearanceController@getInvite');

// // BookingController
// Route::get('booking/approve/{token}','BookingController@approve');
// Route::get('booking/deny/{token}','BookingController@deny');

// //PhotosController
//Route::get('photos/{spaceID}', 'PhotosController@getPhotos');
//Route::post('photo', 'PhotosController@storePhotos');
// Route::post('deletePhoto/{spaceID}/{id}', 'PhotosController@deletePhoto');

// Route::any('{path?}', 'MainController@index')->where("path", ".+");
