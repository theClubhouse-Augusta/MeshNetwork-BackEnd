<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
// /*
// |--------------------------------------------------------------------------
// | API Routes
// |--------------------------------------------------------------------------
// | Here is where you can register API routes for your application. These
// | routes are loaded by the RouteServiceProvider within a group which
// | is assigned the "api" middleware group. Enjoy building your API!
// |
// */
// SpaceController
Route::resource('workspaces', 'SpaceController');
// PhotoController
Route::resource('photos', 'PhotoController');
// DashboardController
Route::get('joins/{slug}', 'DashboardController@Joins');
Route::get('appearances/{slug}', 'DashboardController@Appearances');
Route::get('events/metrics/{slug}', 'DashboardController@Events');
Route::get('appearances/range/{spaceId}/{startMonth}/{startYear}/{endMonth}/{endYear}', 'DashboardController@appearanceForMonthYear');
Route::get('appearances/users/{spaceId}/{month}/{year}/{day}/{endMonth}/{endYear}/{endDay}', 'DashboardController@getUserCheckins');
Route::get('signups/{spaceId}/{month}/{year}/{day}/{endMonth}/{endYear}/{endDay}', 'DashboardController@getUserSignUps');
Route::get('customers/signups/{spaceId}/{month}/{year}/{day}/{endMonth}/{endYear}/{endDay}', 'DashboardController@getCustomerSignUps');
Route::get('balance/current/{pastMonth}/{now}', 'DashboardController@allBalancesFromDate');
Route::get('customers/month/{pastMonth}/{now}', 'DashboardController@getThisMonthsCustomers');
// CompanyController
Route::get('company/{id}', 'CompanyController@getCompany'); // show logged in user
Route::post('company/create', 'CompanyController@store'); // show logged in user
Route::post('company/update/{id}', 'CompanyController@store'); // show logged in user
Route::get('companies', 'CompanyController@companies'); // show logged in user
Route::get('verticals', 'CompanyController@allVerticals'); // show logged in user
Route::get('company/user/get', 'CompanyController@getCompanyOfLoggedInUser'); // show logged in user
Route::post('calendarInvite', 'EventController@calendarInvite'); // show logged in user
// AuthController
Route::get('users', 'AuthController@getUsers');  // admin get users
Route::get('customers', 'AuthController@allCustomers');  // sign up
Route::get('authorize', 'AuthController@checkAuth');  // sign up
Route::post('signUp', 'AuthController@signUp');  // sign up
Route::post('login', 'AuthController@signIn');  // login
Route::post('forgotpassword', 'AuthController@resetPassword');
// UserController
Route::post('customer/email', 'UserController@updateCustomerMeshEmail'); // logged in user profile update
Route::get('user/auth', 'AuthController@getUser'); // get Auth User
Route::get('user/profile/{id}', 'UserController@user'); // get user.id
Route::get('showuser/{id}', 'UserController@showUser'); // show logged in user
Route::post('user/update', 'UserController@updateUser'); // logged in user profile update
Route::get('getDashboardUsers/{id}', 'UserController@getDashboardUsers');
Route::post('changeRole', 'UserController@changeRole');
Route::get('getKioskUsers', 'UserController@getKioskUsers');
// TESTING
Route::get('userskills', 'UserController@userSkills'); // to populate tags in sign up form
Route::get('skills', 'UserController@getSkills'); // to populate tags in sign up form
Route::get('skills/all', 'UserController@allSkills'); // to populate tags in sign up form
Route::post('search', 'UserController@search'); // search by skill/SpaceID
Route::get('organizers/events', 'UserController@OrganizersForEvents'); // to populate tags in sign up form
Route::get('users/{spaceID}', 'UserController@getSpaceUsers');
Route::get('users/space/{spaceID}', 'UserController@usersFromSpace'); // get users from spaceID (for Kiosk)
Route::get('workspace/{slugOrSpaceID}', 'WorkspaceController@show');
Route::get('workspace/auth/{slugOrSpaceID}', 'WorkspaceController@showAuth');
Route::get('events/space/{spaceID}', 'WorkspaceController@events');
Route::get('plans/{slug}', 'WorkspaceController@getSubscriptions');
Route::get('publickey/{slug}', 'WorkspaceController@getKey');
Route::get('organizers/space/{spaceID}', 'WorkspaceController@spaceOrganizers');
Route::get('/space/metrics/{spaceID}', 'WorkspaceController@getSpaceStats');
Route::get('sponsors', 'EventController@Sponsers');
Route::post('event', 'EventController@store');
Route::get('event/{eventID}', 'EventController@show');
Route::get('spaceEvents/{spaceID}', 'WorkspaceController@getSpaceEvents'); //Formatted Events for the Calendar
Route::get('spacename/{spaceID}', 'WorkspaceController@getName');
Route::get('attend/{eventID}', 'EventController@attend');
Route::post('deleteEvent/{id}', 'EventController@deleteEvent');
Route::post('updateEvent', 'EventController@updateEvent');
Route::get('todayevent/{spaceID}', 'EventController@getTodaysEvents');
Route::get('events/{spaceID}', 'EventController@getDashboardEvents');
Route::post('appearance', 'AppearanceController@store');
Route::get('resources/{spaceID}', 'BookingController@getResources');
Route::post('resource', 'BookingController@storeResource');
Route::post('resource/delete/{id}', 'BookingController@deleteResource');
Route::get('occasions', 'AppearanceController@getValidOccasions');
Route::get('bookings/{resourceID}', 'BookingController@getBookings');
Route::post('booking', 'BookingController@store');
//Challenge Routes
Route::get('getCategories', 'CategoriesController@index');
Route::get('selectCategories', 'CategoriesController@select');
Route::post('storeCategory', 'CategoriesController@store');
Route::get('showCategory/{id}/{type}', 'CategoriesController@show');
Route::get('getChallenges/{count}', 'ChallengesController@index');
Route::post('storeChallenge', 'ChallengesController@store');
Route::post('updateChallenge/{id}', 'ChallengesController@update');
Route::get('showChallenge/{id}', 'ChallengesController@show');
Route::post('searchChallenges', 'ChallengesController@search');
Route::get('joinChallenge/{id}', 'ChallengesController@joinChallenge');
Route::post('uploadFile', 'ChallengesController@uploadFile');
Route::get('getSubmissions/{id}', 'ChallengesController@getSubmissions');
Route::post('storeSubmission', 'ChallengesController@storeSubmission');
Route::post('deleteSubmission/{id}', 'ChallengesController@deleteSubmission');
//LMS Routes
Route::get('getCourses/{category}/{count}', 'CoursesController@getCourses');
Route::get('myCourses/{category}', 'CoursesController@getMyCourses');
Route::post('searchCourse', 'CoursesController@searchCourse');
Route::post('storeCourse', 'CoursesController@storeCourse');
Route::get('editCourse/{id}', 'CoursesController@editCourse');
Route::post('updateCourse/{id}', 'CoursesController@updateCourse');
Route::post('updateCourseImage/{id}', 'CoursesController@updateCourseImage');
Route::post('updateCourseInstructorAvatar/{id}', 'CoursesController@updateCourseInstructorAvatar');
Route::post('deleteCourse/{id}', 'CoursesController@deleteCourse');
Route::get('showCourse/{id}/{uid}', 'CoursesController@showCourse');
Route::get('detailCourse/{id}', 'CoursesController@detailCourse');
Route::post('completeLecture', 'CoursesController@completeLecture');
Route::post('completeCourse', 'CoursesController@completeCourse');
Route::post('storeLesson', 'CoursesController@storeLesson');
Route::post('updateLesson/{id}', 'CoursesController@updateLesson');
Route::post('deleteLesson/{id}', 'CoursesController@deleteLesson');
Route::post('storeLecture', 'CoursesController@storeLecture');
Route::post('updateLecture/{id}', 'CoursesController@updateLecture');
Route::post('deleteLecture/{id}', 'CoursesController@deleteLecture');
Route::post('storeFiles', 'CoursesController@storeFiles');
Route::post('deleteFile/{id}', 'CoursesController@deleteFile');
Route::post('storeQuestion', 'CoursesController@storeQuestion');
Route::post('updateQuestion/{id}', 'CoursesController@updateQuestion');
Route::post('deleteQuestion/{id}', 'CoursesController@deleteQuestion');
Route::post('storeAnswer', 'CoursesController@storeAnswer');
Route::post('updateAnswer/{id}', 'CoursesController@updateAnswer');
Route::post('updateCorrectAnswer/{id}/{lid}/{aid}', 'CoursesController@updateCorrectAnswer');
Route::post('deleteAnswer/{id}', 'CoursesController@deleteAnswer');
Route::get('getSubjects', 'CoursesController@getSubjects');
Route::get('enrollCourse/{id}', 'CoursesController@enrollCourse');
Route::get('publishCourse/{id}', 'CoursesController@publishCourse');
Route::get('getCourseStudent/{cid}/{uid}', 'CoursesController@getCourseStudent');
Route::get('approveAnswer/{qid}/{uid}/{i}', 'CoursesController@approveAnswer');