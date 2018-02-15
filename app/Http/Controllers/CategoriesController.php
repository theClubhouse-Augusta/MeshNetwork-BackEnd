<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Purifier;
use Response;
use Auth;
use Image;

use App\Category;
use App\User;
use App\Challenge;
//use App\Question;
use App\Cbind;
//use App\Qbind;
use App\Workspace;

class CategoriesController extends Controller
{
  public function __construct()
  {
    $this->middleware('jwt.auth', ['only' => ['store']]);
  }

  public function index()
  {
    $categories = Category::all();

    return Response::json(['categories' => $categories]);
  }

  public function select()
  {
    $categories = Category::select('id', 'categoryName')->get();

    $categoriesArray = [];
    foreach($categories as $key => $c)
    {
      $categoriesArray[$key]['value'] = $c->id;
      $categoriesArray[$key]['label'] = $c->categoryName;
    }

    return Response::json(['categories' => $categoriesArray]);
  }

  public function store(Request $request)
  {
    $rules = [
      'categoryName' => 'required',
      'categoryImage' => 'required',
    ];

    $validator = Validator::make(Purifier::clean($request->all()), $rules);
    if($validator->fails())
    {
      return Response::json(['error' => 'Please fill out all fields.']);
    }

    $user = Auth::user();
    if($user->roleID != 1) {
      return Response::json(['error' => 'You do not have permission.']);
    }

    $categoryName = $request->input('categoryName');
    $categoryImage = $request->file('categoryImage');
    $categoryColor = '#FFFFFF';
    $categoryTextColor = '#555555';

    $categorySlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $categoryName)));
    $categoryCount = 0;

    $imageFile = 'categories';
    if (!is_dir($imageFile)) {
      mkdir($imageFile,0777,true);
    }

    $imageName = str_random(4);
    if($categoryImage->getClientSize() > 5242880)
    {
      return Response::json(['error' => 'This image is too large.']);
    }
    if($categoryImage->getClientMimeType() != "image/png" && $categoryImage->getClientMimeType() != "image/jpeg" && $categoryImage->getClientMimeType() != "image/gif")
    {
      return Response::json(['error' => 'Not a valid PNG/JPG/GIF image.']);
    }
    $ext = $categoryImage->getClientOriginalExtension();
    $categoryImage->move($imageFile, $imageName.'.'.$ext);
    $categoryImage = $imageFile.'/'.$imageName.'.'.$ext;
    $img = Image::make($categoryImage);
    list($width, $height) = getimagesize($categoryImage);
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
    $img->save($categoryImage);

    $category = new Category;
    $category->categoryName = $categoryName;
    $category->categoryImage = $request->root().'/'.$categoryImage;
    $category->categorySlug = $categorySlug;
    $category->categoryColor = $categoryColor;
    $category->categoryTextColor = $categoryTextColor;
    $category->categoryCount = $categoryCount;
    $category->save();

    $categoryData = Category::find($category->id);

    return Response::json(['category' => $categoryData]);
  }

  public function show($id, $type)
  {
    $category = Category::where('categorySlug', $id)->first();
    $result = [];
    if(!empty($category)) {

      if($type == 'Challenges') {
        $cbinds = Cbind::where('categoryID', $category->id)->paginate(30);

        foreach($cbinds as $key => $c)
        {
          $challenge = Challenge::where('challenges.status', 'Approved')->where('challenges.id', $c->challengeID)->join('workspaces', 'challenges.spaceID', '=', 'workspaces.id')
            ->select(
              'challenges.id',
              'challenges.challengeImage',
              'challenges.challengeTitle',
              'challenges.challengeContent',
              'challenges.challengeSlug',
              'challenges.spaceID',
              'challenges.startDate',
              'challenges.endDate',
              'workspaces.avatar',
              'workspaces.name',
              'workspaces.city'
            )
            ->orderBy('challenges.created_at', 'DESC')
            ->first();

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

          $result[] = $challenge;
        }

        return Response::json(['challenges' => $result]);
      }
      /*else if($type == 'Questions') {
        $qbinds = Qbind::where('categoryID', $category->id)->paginate(30);

        foreach($qbinds as $key => $q)
        {
          $question = Question::where('questions.id', $q->questionID)->join('profiles', 'questions.userID', '=', 'profiles.id')
            ->select(
              'questions.id',
              'questions.questionTitle',
              'questions.questionContent',
              'questions.profileID',
              'questions.questionSlug',
              'questions.questionViews',
              'questions.questionReplies',
              'profiles.avatar',
              'profiles.profileName',
              'profiles.profileTitle'
            )
            ->orderBy('questions.created_at', 'DESC')
            ->first();

          $categories = Qbind::where('qbinds.challengeID', $question->id)->join('categories', 'qbinds.categoryID', '=', 'categories.id')
            ->select(
              'categories.id',
              'categories.categorySlug',
              'categories.categoryName',
              'categories.categoryColor',
              'categories.categoryTextColor'
            )
            ->get();

          $question->categories = $categories;
          $question->questionContent =  substr(strip_tags($question->questionContent),0, 200);

          $result[] = $question;
        }
      }*/
      return Response::json(['questions' => $result]);
    }
  }
}
