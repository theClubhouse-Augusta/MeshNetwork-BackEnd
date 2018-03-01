<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Response;
use Purifier;
use Auth;
use JWTAuth;
use Image;

use App\User;
use App\Course;
use App\Lesson;
use App\Lecture;
use App\Enroll;
use App\Complete;
use App\Question;
use App\Answer;
use App\Solution;
use App\Document;
use App\Subject;

class CoursesController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth', ['only' => [
            'storeCourse',
            'updateCourse',
            'deleteCourse',
            'updateCourseImage',
            'updateCourseInstructorAvatar',
            'getMyCourses',
            'showCourse',
            'editCourse',
            'updateCorrectAnswer',
            'completeLecture',
            'completeCourse', 
            'storeLesson',
            'updateLesson',
            'deleteLesson',
            'storeLecture', 
            'updateLecture',
            'deleteLecture',
            'storeFiles', 
            'deleteFile',
            'storeQuestion',
            'updateQuestion',
            'deleteQuestion',
            'storeAnswer',
            'updateAnswer',
            'deleteAnswer',
            'enrollCourse',
            'publishCourse'
        ]]);
    }

    public function getCourses($category, $count)
    {
        if($category == 0)
        {
            $courses = Course::where('archive', 0)->where('courseStatus', 'Published')->select('id', 'courseName', 'courseCategory', 'courseSummary', 'courseImage')->paginate($count);
        }
        else {
            $courses = Course::where('archive', 0)->where('courseCategory', $category)->where('courseStatus', 'Published')->select('id', 'courseName', 'courseCategory', 'courseSummary', 'courseImage')->paginate($count);
        }

        return Response::json(['courses' => $courses]);
    }

    public function storeCourse(Request $request)
    {
        $user = Auth::user();

        $userID = $user->id;
        $courseCategory = 0;
        $courseName = $request->input('courseName');
        $courseSummary = $request->input('courseSummary');
        $courseInformation = $request->input('courseInformation');
        $courseInstructorName = $request->input('courseInstructorName');
        $courseInstructorInfo = $request->input('courseInstructorInfo');
        $courseStatus = 'Draft';
        $archive = 0;

        $course = new Course;
        $course->courseCategory = $courseCategory;
        $course->userID = $userID;
        $course->courseName = $courseName;
        $course->courseSummary = $courseSummary;
        $course->courseInformation = $courseInformation;
        $course->courseInstructorName = $courseInstructorName;
        $course->courseInstructorInfo = $courseInstructorInfo;
        $course->courseStatus = $courseStatus;
        $course->archive = $archive;
        $course->save();

        return Response::json(['course' => $course->id]);
    }

    public function updateCourse(Request $request, $id)
    {
        $user = Auth::user();
        $course = Course::find($id);

        if($user->id != $course->userID)
        {
            return Response::json(['error' => 'You do not have permission.']);
        }

        if($request->has('courseName'))
        {
            $courseName = $request->input('courseName');
        } else {
            $courseName = $course->courseName;
        }

        if($request->has('courseCategory'))
        {
            $courseCategory = $request->input('courseCategory');
        } else {
            $courseCategory = $course->courseCategory;
        }

        if($request->has('courseSummary'))
        {
            $courseSummary = $request->input('courseSummary');
        } else {
            $courseSummary = $course->courseSummary;
        }

        if($request->has('courseInformation'))
        {
            $courseInformation = $request->input('courseInformation');
        } else {
            $courseInformation = $course->courseInformation;
        }

        if($request->has('courseInstructorName'))
        {
            $courseInstructorName = $request->input('courseInstructorName');
        } else {
            $courseInstructorName = $course->courseInstructorName;
        }

        if($request->has('courseInstructorInfo'))
        {
            $courseInstructorInfo = $request->input('courseInstructorInfo');
        } else {
            $courseInstructorInfo = $course->courseInstructorInfo;
        }

        if($request->has('courseStatus'))
        {
            $courseStatus = $request->input('courseStatus');
        } else {
            $courseStatus = $course->courseStatus;
        }

        if($request->has('coursePrice'))
        {
            $coursePrice = $request->input('coursePrice');
        } else {
            $coursePrice = $course->coursePrice;
        }

        $archive = 0;

        $course->courseCategory = $courseCategory;
        $course->courseName = $courseName;
        $course->courseSummary = $courseSummary;
        $course->courseInformation = $courseInformation;
        $course->courseInstructorName = $courseInstructorName;
        $course->courseInstructorInfo = $courseInstructorInfo;
        $course->coursePrice = $coursePrice;
        $course->courseStatus = $courseStatus;
        $course->archive = $archive; 
        $course->save();

        return Response::json(['success' => 'Course Updated']);
    }

    public function deleteCourse($id)
    {
        $user = Auth::user();

        $course = Course::find($id);
        if($course->userID != $user->id) {
            return Response::json(['error' => 'You do not have permission.']);
        }

        $course->archive = 1;
        $course->save();

        return Response::json(['success' => 'Course Removed']);
    }
    

    public function updateCourseImage(Request $request, $id)
    {
        $rules = [
            'courseImage' => 'required'
        ];

        $validator = Validator::make(Purifier::clean($request->all()), $rules);

        if ($validator->fails()) {
            return Response::json(['error' => 'Please fill out all fields.']);
        }

        $user = Auth::user();
        $course = Course::find($id);

        if($user->id != $course->userID)
        {
            return Response::json(['error' => 'You do not have permission.']);
        }

        $imageFile = 'storage/course';
        if (!is_dir($imageFile)) {
            mkdir($imageFile, 0777, true);
        }
        $thumbnailFile = 'storage/course/thumbnails';
        if (!is_dir($thumbnailFile)) {
            mkdir($thumbnailFile, 0777, true);
        }

        $string = str_random(15);
        $topicImg = $request->file('courseImage');
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

        $course->courseImage = $topicImg;
        $course->courseThumbnail = $topicThumbnail;
        $course->save();

        return Response::json(['success' => 'Image Uploaded']);
    }

    public function updateCourseInstructorAvatar(Request $request, $id)
    {
        $rules = [
            'courseInstructorAvatar' => 'required'
        ];

        $validator = Validator::make(Purifier::clean($request->all()), $rules);

        if ($validator->fails()) {
            return Response::json(['error' => 'Please fill out all fields.']);
        }

        $user = Auth::user();
        $course = Course::find($id);

        if($user->id != $course->userID)
        {
            return Response::json(['error' => 'You do not have permission.']);
        }

        $imageFile = 'storage/course/instructors';
        if (!is_dir($imageFile)) {
            mkdir($imageFile, 0777, true);
        }
        $thumbnailFile = 'storage/course/instructors';
        if (!is_dir($thumbnailFile)) {
            mkdir($thumbnailFile, 0777, true);
        }

        $string = str_random(15);
        $topicImg = $request->file('courseInstructorAvatar');
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
            if ($height > 500) {
                $img->crop(500, 500);
            }
        }
        $img->save($topicThumbnail);

        if ($topicImg != null) {
            $topicImg = $request->root() . '/' . $topicImg;
        }

        if ($topicThumbnail != null) {
            $topicThumbnail = $request->root() . '/' . $topicThumbnail;
        }

        $course->courseInstructorAvatar = $topicThumbnail;
        $course->save();

        return Response::json(['success' => 'Image Uploaded']);
    }

    public function searchCourse(Request $request)
    {
        $rules = [
            'searchContent' => 'required'
        ];

        $validator = Validator::make(Purifier::clean($request->all()), $rules);

        if ($validator->fails()) {
            return Response::json(['error' => 'Please fill out all fields.']);
        }

        $searchContent = $request->input('courseContent');

        $courses = Course::where('courseName', 'LIKE', '%'.$courseContent.'%')->orWhere('courseInformation', 'LIKE', '%'.$courseContent.'%')->get();

        return Response::json($courses);
    }

    public function getMyCourses(Request $request)
    {
        $user = Auth::user();

        if($user->roleID == 3)
        {
            $courses = Enroll::where('enrolls.userID', $user->id)->join('courses', 'enrolls.courseID', '=', 'courses.id')->select('courses.id', 'courses.courseName', 'courses.userID', 'courses.courseCategory', 'courses.courseSummary', 'courses.courseImage')->paginate(16);

            foreach($courses as $courkey => $course)
            {
                $complete = 0;
                $percent = 0;
                $lectureCount = 0;

                $lessons = Lesson::where('courseID', $course->id)->get();
                foreach($lessons as $leskey => $lesson)
                {
                    $lectures = Lecture::where('lessonID', '=', $lesson->id)->get();
                    $lectureCount = count($lectures);
                    foreach($lectures as $lecKey => $lecture)
                    {
                        $completes = Complete::where('userID', '=', $user->id)->where('lectureID', '=', $lecture->id)->get();
                        if(!$completes->isEmpty())
                        {
                            $complete = count($completes);
                        }
                    }
                }

                if($lectureCount > 0)
                {
                    $course->percent = $complete/$lectureCount * 100;
                    $course->complete = $complete . '/' . $lectureCount;
                }
            }

        }
        else if($user->roleID == 4 || $user->roleID == 2 || $user->roleID == 1)
        {
            $courses = Course::where('archive', '=', 0)->where('userID', '=', $user->id)->select('id', 'userID', 'courseName', 'courseCategory', 'courseSummary', 'courseImage', 'courseStatus')->paginate(12);
        }

        return Response::json(['courses' => $courses]);
    }

    public function detailCourse($id)
    {
        $course = Course::where('id', $id)->where('archive', '=', 0)->first();
        
        if(!empty($course))
        {
            $lessons = Lesson::where('courseID', $course->id)->get();
            $lectures = [];

            if(!$lessons->isEmpty())
            {
                foreach($lessons as $lKey => $lesson)
                {
                    $lecture = Lecture::where('lessonID', $lesson->id)->select('id', 'lessonID', 'lectureName', 'lectureType')->get();
                    if(!$lecture->isEmpty())
                    {
                        foreach($lecture as $lecKey => $l)
                        {
                            $lectures[] = $l;
                        }
                    }
                }
            }
        }
        return Response::json(['course' => $course, 'lessons' => $lessons, 'lectures' => $lectures]);
    }

    public function showCourse($id)
    {
        $course = Course::where('id', $id)->where('archive', '=', 0)->first();
        $user = Auth::user();

        if(!empty($course))
        {
            $lessons = Lesson::where('courseID', $course->id)->get();
            $lectures = [];
            $students = [];
            $files = [];
            $questions = [];
            $answers = [];
            $complete = 0;
            $percent = 0;
            $enrolled = 0;

            if(!$lessons->isEmpty())
            {
                $enroll = Enroll::where('userID', $user->id)->where('courseID', $id)->first();

                if(!empty($enroll) || $course->userID == $user->id)
                {
                    $enrolled = 1;

                    foreach($lessons as $lesKey => $lesson)
                    {
                        $lecture = Lecture::where('lessonID', $lesson->id)->get();
                        if(!$lecture->isEmpty())
                        {
                            foreach($lecture as $lecKey => $l)
                            {
                                $lecComplete = Complete::where('userID', $user->id)->where('lectureID', $l->id)->first();
                                $lecStatus = 0;

                                if(!empty($lecComplete))
                                {
                                    $lecStatus = $lecStatus + 1;
                                    $l->complete = 1;
                                    $l->grade = $lecComplete->grade;
                                }
                                else {
                                    $l->complete = 0;
                                    $l->grade = 0;
                                }

                                $l->status = $lecStatus;

                                $lectures[] = $l;

                                $filesGet = Document::where('lectureID', $l->id)->get();
                                if(!$filesGet->isEmpty())
                                {
                                    foreach($filesGet as $filKey => $lectureFile)
                                    {
                                        if($lectureFile->lectureID == $l->id)
                                        {
                                            $files[] = $lectureFile;
                                        }
                                    }
                                }

                                $questionsGet = Question::where('lectureID', $l->id)->get();
                                if(!$questionsGet->isEmpty())
                                {
                                    foreach($questionsGet as $qKey => $question)
                                    {
                                        $questions[] = $question;

                                        $answersGet = Answer::where('questionID', $question->id)->get();
                                        if(!$answersGet->isEmpty())
                                        {
                                            foreach($answersGet as $aKey => $answer)
                                            {
                                                $answers[] = $answer;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $enrolls = Enroll::where('courseID', $id)->get();

                    foreach($enrolls as $eKey => $enroll)
                    {
                        $profile = User::where('id', $enroll->userID)->first();
                        $complete = 0;

                        foreach($lessons as $lKey => $lesson)
                        {
                            $lecture = Lecture::where('lessonID', $lesson->id)->get();
                            if(!$lecture->isEmpty())
                            {
                                foreach($lecture as $lecKey => $l)
                                {
                                    $completes = Complete::where('userID', $enroll->userID)->where('lectureID', $l->id)->get();
                                    $complete = $complete + count($completes);
                                }
                            }
                        }

                        $percent = $complete/count($lectures) * 100;
                        $complete = $complete . '/' . count($lectures);
                        $student = [
                            'profile' => $profile,
                            'status' => $enroll->status,
                            'complete' => $complete,
                            'percent' => $percent
                        ];
                        $student = json_encode($student);
                        $students[] = $student;
                    }
                }
                else {
                    foreach($lessons as $lKey => $lesson)
                    {
                        $lecture = Lecture::where('lessonID', $lesson->id)->get();
                        if(!$lecture->isEmpty())
                        {
                            foreach($lecture as $lecKey => $l)
                            {
                                $lectures[] = $l;
                            }
                        }
                    }
                }
            }

            return Response::json(['course' => $course, 'lessons' => $lessons, 'lectures' => $lectures, 'students' => $students, 'questions' => $questions, 'answers' => $answers, 'files' => $files, 'enrolled' => $enrolled]);
        }

        else {
            return Response::json(['course' => [], 'lessons' => [], 'lectures' => []]);
        }
    }

    public function editCourse($id)
    {
        $user = Auth::user();
        $course = Course::find($id);

        if($user->id != $course->userID)
        {
            return Response::json(['error' => 'You do not have permission']);
        }

        $lectures = [];
        $documents = [];
        $questions = [];
        $answers = [];

        $course = Course::where('archive', 0)->where('id', $id)->first();
        $lessons = Lesson::where('courseID', $course->id)->get();

        if(!$lessons->isEmpty())
        {
            foreach($lessons as $lesKey => $lesson)
            {
                $lecturesGet = Lecture::where('lessonID', $lesson->id)->get();
                if(!$lecturesGet->isEmpty())
                {
                    foreach($lecturesGet as $lecKey => $lecture)
                    {
                        $lectures[] = $lecture;

                        $docsGet = Document::where('lectureID', $lecture->id)->get();
                        if(!$docsGet->isEmpty())
                        {
                            foreach($docsGet as $docsKey => $lectureFile)
                            {
                                $documents[] = $lectureFile;
                            }
                        }

                        $questionsGet = Question::where('lectureID', $lecture->id)->get();
                        if(!$questionsGet->isEmpty())
                        {
                            foreach($questionsGet as $quesKey => $question)
                            {
                                $questions[] = $question;

                                $answersGet = Answer::where('questionID', $question->id)->get();
                                if(!$answersGet->isEmpty())
                                {
                                    foreach($answersGet as $ansKey => $answer)
                                    {
                                        $answers[] = $answer;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return Response::json(['course' => $course, 'lessons' => $lessons, 'lectures' => $lectures, 'questions' => $questions, 'answers' => $answers, 'files' => $documents]);
    }

    public function updateCorrectAnswer($id, $lid, $aid)
    {
        $course = Course::find($id);
        $user = Auth::user();

        if($course->userID != $user->id)
        {
            return Response::json(['error' => 'You do not have permission']);
        }

        $answers = Answer::where('questionID', $lid)->get();
        foreach($answers as $aKey => $answer) {
            $answer->isCorrect = 0;
            $answer->save();
        }

        $answer = Answer::where('id', $aid)->first();
        $answer->isCorrect = 1;
        $answer->save();

        return Response::json(['success' => "Answer Updated."]);
    }

    public function completeLecture(Request $request)
    {
        $courseID = $request->input('courseID');
        $lectureID = $request->input('lectureID');
        $answers = $request->input('answers');

        $course = Course::find($courseID);
        $user = Auth::user();

        /*if($course->userID != $user->id)
        {
            return Response::json(['error' => 'You do not have permission']);
        }*/

        $enroll = Enroll::where('userID', $user->id)->where('courseID', $courseID)->first();

        if(empty($enroll)) {
            return Response::json(['error' => 'You are not enrolled in this course.']);
        }

        $lecture = Lecture::find($lectureID);
        $completeCheck = Complete::where('lectureID', $lecture->id)->where('userID', $user->id)->first();

        if(empty($completeCheck))
        {
            if($lecture->lectureType == "Exam")
            {
                $questionCount = 0;
                $answerCount = 0;

                $questions = Question::where('lectureID', $lectureID)->get();
                $questionCount = count($questions);

                if(empty($answers))
                {
                    return Response::json(['error' => 'Please answer all questions.']);
                }

                foreach($questions as $quesKey => $question)
                {
                    $answerGet = Answer::where('questionID', $question->id)->get();

                    foreach($answerGet as $ansKey => $a)
                    {
                        foreach($answers as $aKey => $answer)
                        {
                            $solution = new Solution;
                            $solution->userID = $user->id;
                            $solution->questionID = $question->id;
                            $solution->answer = $answer->answerID;
                            $solution->save();

                            if($a->id == $answer->answerID && $a->isCorrect == 1)
                            {
                                $answerCount = $answerCount + 1;
                            }
                        }
                    }
                }

                $grade = $answerCount/$questionCount * 100;
                $complete = new Complete;
                $complete->userID = $user->id;
                $complete->lectureID = $lectureID;
                $complete->grade = $grade;
                $complete->save();

                return Response::json(['success' => 'You have completed this exam', 'grade' => $grade]);
            }
            else {
                $complete = new Complete;
                $complete->userID = $user->id;
                $complete->lectureID = $lectureID;
                $complete->grade = 100;
                $complete->save();

                return Response::json(['success' => 'You have completed this lecture.']);
            }
        }
        else {
            return Response::json(['success' => 'You have already completed this Exam.', 'grade' => $completeCheck->grade]);
        }
    }

    public function completeCourse(Request $request)
    {
        $courseID = $request->input('courseID');
        $course = Course::find($courseID);
        $user = Auth::user();

        $enroll = Enroll::where('userID', $user->id)->where('courseID', $courseID)->first();

        if(empty($enroll))
        {
            return Response::json(['error' => 'You are not enrolled in this course']);
        }

        $lectureCount = 0;
        $completeCount = 0;
        $exams = [];
        $lessons = Lesson::where('courseID', $courseID)->get();

        foreach($lessons as $lesKey => $lesson)
        {
            $lectures = Lecture::where('lessonID', $lesson->id)->get();
            foreach($lectures as $lecKey => $lecture)
            {
                $lectureCount = $lectureCount + 1;
                if($lecture->lectureType == "Exam")
                {
                    $exams[] = $lecture;
                }

                $completes = Complete::where('userID', $user->id)->where('lectureID', $lecture->id)->get();
                foreach($completes as $comKey => $complete)
                {
                    $completeCount = $completeCount + 1;
                }
            }
        }

        $totalGrade = 0;
        foreach($exams as $eKey => $exam)
        {
            $completes = Complete::where('userID', $user->id)->where('lectureID', $exam->id)->get();
            foreach($completes as $comkey => $complete)
            {
                $totalGrade = $totalGrade + $complete->grade;
            }
        }

        $averageGrade = $totalGrade / count($exams);

        if($lectureCount == $completeCount && $averageGrade >= 64.00)
        {
            $enrollUpdate = Enroll::where('userID', $user->id)->where('courseID', $courseID)->first();
            $enrollUpdate->status = "Graduate";
            $enrollUpdate->save();

            return Response::json(['success' => 'You have completed this course!']);
        }
        else {
            return Response::json(['error' => "You have not completed this course."]);
        }
    }

    public function storeLesson(Request $request)
    {
        $courseID = $request->input('courseID');
        $lessonName = $request->input('lessonName');
        
        $course = Course::find($courseID);
        $user = Auth::user();

        if($course->userID != $user->id)
        {
            return Response::json(['error' => 'You do not have permission']);
        }

        $lesson = new Lesson;
        $lesson->courseID = $courseID;
        $lesson->lessonName = $lessonName;
        $lesson->save();

        return Response::json(['success' => $lesson->id]);
    }

    public function updateLesson(Request $request, $id)
    {
        $lesson = Lesson::find($id);
        $course = Course::find($lesson->courseID);
        $user = Auth::user();

        if($course->userID != $user->id)
        {
            return Response::json(['error' => 'You do not have permission']);
        }

        $lessonName = $request->input('lessonName');
        $lesson->lessonName = $lessonName;
        $lesson->save();

        return Response::json(['success' => $lessonName]);
    }

    public function deleteLesson($id)
    {
        $lesson = Lesson::find($id);
        $course = Course::find($lesson->courseID);
        $user = Auth::user();

        if($course->userID != $user->id)
        {
            return Response::json(['error' => 'You do not have permission']);
        }

        $lesson->delete();

        return Response::json(['success' => 'Lesson Deleted']);
    }

    public function storeLecture(Request $request)
    {
        $lessonID = $request->input('lessonID');
        $lectureName = $request->input('lectureName');
        $lectureContent = $request->input('lectureContent');
        $lectureType = $request->input('lectureType');

        $lesson = Lesson::find($lessonID);
        $course = Course::find($lesson->courseID);
        $user = Auth::user();

        if($course->userID != $user->id)
        {
            return Response::json(['error' => 'You do not have permission']);
        }

        $lecture = new Lecture;
        $lecture->lessonID = $lessonID;
        $lecture->lectureName = $lectureName;
        $lecture->lectureContent = $lectureContent;
        $lecture->lectureType = $lectureType;
        $lecture->save();

        return Response::json(['success' => $lecture->id]);
    }
    
    public function updateLecture(Request $request, $id)
    {
        $lectureName = $request->input('lectureName');
        $lectureContent = $request->input('lectureContent');
        $lectureType = $request->input('lectureType');
        $lectureVideo = $request->input('lectureVideo');

        $lecture = Lecture::find($id);
        $lesson = Lesson::find($lecture->lessonID);
        $course = Course::find($lesson->courseID);
        $user = Auth::user();

        if($course->userID != $user->id)
        {
            return Response::json(['error' => 'You do not have permission']);
        }

        $lecture->lectureName = $lectureName;
        $lecture->lectureContent = $lectureContent;
        $lecture->lectureType = $lectureType;
        $lecture->lectureVideo = $lectureVideo;
        $lecture->save();

        $lectureData = Lecture::find($lecture->id);

        return Response::json(['lecture' => $lectureData]);
    }

    public function deleteLecture($id)
    {
        $lecture = Lecture::find($id);
        $lesson = Lesson::find($lecture->lessonID);
        $course = Course::find($lesson->courseID);
        $user = Auth::user();

        if($course->userID != $user->id)
        {
            return Response::json(['error' => 'You do not have permission']);
        }

        $lecture->delete();

        return Response::json(['success' => 'Lecture Deleted']);
    }

    public function storeFiles(Request $request)
    {
        $lectureID = $request->input('lectureID');
        $fileData = $request->file('fileContent');

        $lecture = Lecture::find($lectureID);
        $lesson = Lesson::find($lecture->lessonID);
        $course = Course::find($lesson->courseID);
        $user = Auth::user();

        if($course->userID != $user->id)
        {
            return Response::json(['error' => 'You do not have permission']);
        }

        $doc = new Document;
        $doc->lectureID = $lectureID;
        $doc->fileData = $fileData;
        $doc->save();

        return Response::json(['success' => $doc->id]);

    }

    public function deleteFile($id)
    {
        $doc = Document::find($id);
        $lecture = Lecture::find($doc->lectureID);
        $lesson = Lesson::find($lecture->lessonID);
        $course = Course::find($lesson->courseID);
        $user = Auth::user();

        if($course->userID != $user->id)
        {
            return Response::json(['error' => 'You do not have permission']);
        }

        $doc->delete();

        return Response::json(['success' => 'File Deleted']);
    }

    public function storeQuestion(Request $request)
    {
        $questionContent = $request->input('questionContent');
        $questionType = $request->input('questionType');
        $lectureID = $request->input('lectureID');

        $lecture = Lecture::find($lectureID);
        $lesson = Lesson::find($lecture->lessonID);
        $course = Course::find($lesson->courseID);
        $user = Auth::user();

        if($course->userID != $user->id)
        {
            return Response::json(['error' => 'You do not have permission']);
        }

        $question = new Question;
        $question->questionContent = $questionContent;
        $question->questionType = $questionType;
        $question->lectureID = $lectureID;
        $question->save();

        return Response::json(['success' => $question->id]);
    }

    public function updateQuestion(Request $request, $id)
    {
        $questionContent = $request->input('questionContent');

        $question = Question::find($id);
        $lecture = Lecture::find($question->lectureID);
        $lesson = Lesson::find($lecture->lessonID);
        $course = Course::find($lesson->courseID);
        $user = Auth::user();

        if($course->userID != $user->id)
        {
            return Response::json(['error' => 'You do not have permission']);
        }

        $question->questionContent = $questionContent;
        $question->save();

        $questionData = Question::find($question->id);

        return Response::json(['question' => $questionData]);
    }

    public function deleteQuestion($id)
    {
        $question = Question::find($id);
        $lecture = Lecture::find($question->lectureID);
        $lesson = Lesson::find($lecture->lessonID);
        $course = Course::find($lesson->courseID);
        $user = Auth::user();

        if($course->userID != $user->id)
        {
            return Response::json(['error' => 'You do not have permission']);
        }

        $question->delete();

        return Response::json(['success' => 'Question Deleted']);
    }

    public function storeAnswer(Request $request)
    {
        $questionID = $request->input('questionID');
        $answerContent = $request->input('answerContent');
        $isCorrect = $request->input('isCorrect');
        if($isCorrect == 'false')
        {
            $isCorrect = 0;
        }

        $question = Question::find($questionID);
        $lecture = Lecture::find($question->lectureID);
        $lesson = Lesson::find($lecture->lessonID);
        $course = Course::find($lesson->courseID);
        $user = Auth::user();

        if($course->userID != $user->id)
        {
            return Response::json(['error' => 'You do not have permission']);
        }

        $answer = new Answer;
        $answer->questionID = $questionID;
        $answer->answerContent = $answerContent;
        $answer->isCorrect = $isCorrect;
        $answer->save();

        return Response::json(['success' => $answer->id]);
    }

    public function updateAnswer(Request $request, $id)
    {
        $answerContent = $request->input('answerContent');

        $answer = Answer::find($id);
        $question = Question::find($answer->questionID);
        $lecture = Lecture::find($question->lectureID);
        $lesson = Lesson::find($lecture->lessonID);
        $course = Course::find($lesson->courseID);
        $user = Auth::user();

        if($course->userID != $user->id)
        {
            return Response::json(['error' => 'You do not have permission']);
        }

        $answer->answerContent = $answerContent;
        $answer->save();

        $answerData = Answer::find($answer->id);

        return Response::json(['answer' => $answerData]);
    }

    public function deleteAnswer($id)
    {
        $answer = Answer::find($id);
        $question = Question::find($answer->questionID);
        $lecture = Lecture::find($question->lectureID);
        $lesson = Lesson::find($lecture->lessonID);
        $course = Course::find($lesson->courseID);
        $user = Auth::user();

        if($course->userID != $user->id)
        {
            return Response::json(['error' => 'You do not have permission']);
        }

        $answer->delete();

        return Response::json(['success' => 'Answer Deleted']);
    }


    public function getSubjects()
    {
        $subjects = Subject::all();

        return Response::json(['categories' => $subjects]);
    }

    public function enrollCourse($id)
    {
        $user = Auth::user();

        if($user->roleID == 4)
        {
            return Response::json(['error' => 'You cannot enroll on an instructor account.']);
        }

        $enrollCheck = Enroll::where("userID", $user->id)->where('courseID', $id)->first();

        if(empty($enrollCheck))
        {
            $enroll = new Enroll;
            $enroll->userID = $user->id;
            $enroll->courseID = $id;
            $enroll->enrollStatus = 'Current';
            $enroll->save();


            return Response::json(['success' => 'You have been enrolled.']);
        } else {

            return Response::json(['error' => 'You are already enrolled in this class.']);
        }

    }

    public function publishCourse($id)
    {
        $user = Auth::user();
        $course = Course::find($id);

        if($user->id != $course->userID)
        {
            return Response::json(['error' => 'You do not have permission']);
        }

        if($course->courseStatus == 'Published')
        {
            $course->courseStatus = 'Draft';
            $course->save();

            return Response::json(['success' => 'Course Unpublished.']);
        } else if($course->courseStatus == 'Draft')
        {
            $course->courseStatus = 'Published';
            $course->save();
            return Response::json(['success' => 'Course Published.']);
        }

    }


}
