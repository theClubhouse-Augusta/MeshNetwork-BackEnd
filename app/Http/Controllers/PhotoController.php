<?php

namespace App\Http\Controllers;

use App\Photo;
use App\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth;
use JWTAuth;
use Response;
use Purifier;
use Image;

class PhotoController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth', ['only' => [
            'store',
            'destroy',
        ]]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'spaceID' => 'required',
            'photo' => 'required'
        ];

        $validator = Validator::make(Purifier::clean($request->all()), $rules);

        if ($validator->fails()) {
            return Response::json(['error' => 'Please fill out all fields.']);
        }

        $spaceID = $request->input('spaceID');

        $auth = Auth::user();
        $space = Workspace::where('id', $spaceID)->orWhere('slug', $spaceID)->first();
        if ($auth->spaceID != $space->id || $auth->roleID != 2) {
            return Response::json(['error' => 'You do not have permission.']);
        }

        $photos = Photo::where('spaceID', $space->id)->get();
        if (count($photos) >= 10) {
            return Response::json(['error' => 'You may only upload 10 photos per space.']);
        }

        $imageFile = 'storage/gallery';
        if (!is_dir($imageFile)) {
            mkdir($imageFile, 0777, true);
        }
        $thumbnailFile = 'storage/gallery/thumbnails';
        if (!is_dir($thumbnailFile)) {
            mkdir($thumbnailFile, 0777, true);
        }

        $string = str_random(15);
        $topicImg = $request->file('photo');
        $topicImg = Image::make($topicImg);

        if ($topicImg->filesize() > 5242880) {
            return Response::json(['error' => 'One of your images was too large.']);
        }

        if ($topicImg->mime() != "image/png" && $topicImg->mime() != "image/jpeg") {
            return Response::json(['error' => 'Not a valid PNG/JPG/GIF image.']);
        } else {
            if ($topicImg->mime() == "image/png") {
                $ext = "png";
            } else if ($topicImg->mime() == "image/jpeg") {
                $ext = "jpg";
            }
        }

        $topicImg->save($imageFile . '/' . $string . '.' . $ext);
        $topicImg = $imageFile . '/' . $string . '.' . $ext;

        $topicThumbnail = $thumbnailFile . '/' . $string . '_thumbnail.png';
        $img = Image::make($topicImg);

        list($width, $height) = getimagesize($topicImg);
        if ($width > 500) {
            $img->resize(500, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            if ($height > 300) {
                $img->crop(500, 300);
            }
        }
        $img->save($topicThumbnail);

        if ($topicImg != null) {
            $topicImg = $request->root() . '/' . $topicImg;
        }

        if ($topicThumbnail != null) {
            $topicThumbnail = $request->root() . '/' . $topicThumbnail;
        }

        $photo = new Photo;
        $photo->spaceID = $spaceID;
        $photo->photoUrl = $topicImg;
        $photo->photoThumbnail = $topicThumbnail;
        $photo->save();

        $photoData = Photo::find($photo->id);
        return Response::json(['success' => 'Your Photos have been Uploaded.', 'photo' => $photoData]);
    }


    /**
     * Display the specified resource.
     *
     * @param  $spaceID
     * @return \Illuminate\Http\Response
     */
    public function show($spaceID)
    {
        $photos = Photo::where('spaceID', $spaceID)->get();
        return Response::json(['photos' => $photos]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Photo  $photo
     * @return \Illuminate\Http\Response
     */
    public function edit(Photo $photo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Photo  $photo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Photo $photo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Photo $photo
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Request $request, Photo $photo)
    {
        //$validator = Validator::make(Purifier::clean($request->all()), $rules);

        //if ($validator->fails()) {
       //     return Response::json(['error' => 'Please fill out all fields.']);
        //}

        $auth = Auth::user();
        if ($auth->spaceID != $photo->spaceID || $auth->roleID != 2) {
            return Response::json(['error' => 'You do not have permission.']);
        }

//        $photo = Photo::find($id);
        $success = $photo->delete();

        if ($success) {
            return Response::json(['success' => 'Photo has been deleted.']);
        } else {
            return Response::json(['error' => 'photo not deleted']);
        }
        //
    }
}
