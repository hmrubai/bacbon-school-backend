<?php

namespace App\Http\Controllers;

use App\CourseSubject;
use App\LogLectureVideo;
use App\ResultLecture;
use App\LectureVideo;
use App\LogLectureWatchComplete;
use App\User;
use DB;
use App\ReviewExam;
use App\ResultReview;
use Illuminate\Http\Request;
use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;
use App\Imports\UsersImport;
use Excel;
use Carbon\Carbon;
class LogLectureVideoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getTopUserListBasedOnLectureWatchingSpecificStudents (Request $request)
    {

        $list = LogLectureVideo::join('users', 'log_lecture_videos.user_id', 'users.id')
                                ->select(
                                    'users.id',
                                    'users.name',
                                    'users.mobile_number',
                                    'users.email',
                                    'users.gender',
                                    'users.address'
                                    )
                                    ->whereIn('users.id', $request->studentIds)
                                    ->where('log_lecture_videos.course_id', 2)
                                    ->groupBy(
                                        'users.id',
                                        'users.name',
                                        'users.mobile_number',
                                        'users.email',
                                        'users.gender',
                                        'users.address'
                                    )
                                ->selectRaw('sum(log_lecture_videos.duration) as totalSpentTimeOnLecture')
                                ->orderBy('totalSpentTimeOnLecture', 'desc')
                                ->get();


        $quizParticipationList = ResultLecture::join('users', 'result_lectures.user_id', 'users.id')
        ->join('lecture_exams', 'result_lectures.lecture_exam_id', 'lecture_exams.id')
        ->select(
            'users.id',
            'users.name',
            'users.mobile_number',
            'users.email',
            'users.gender',
            'users.address'
            )
            ->whereIn('users.id', $request->studentIds)
            ->where('lecture_exams.course_id', 2)
            ->groupBy(
                'users.id',
                'users.name',
                'users.mobile_number',
                'users.email',
                'users.gender',
                'users.address'
            )
        ->selectRaw('count(result_lectures.id) as totalParticipation')
        ->orderBy('totalParticipation', 'desc')
        ->get();
        $obj = (Object) [
            "lectureWiseUsers" => $list,
            "quizParticipationWiseUsers" => $quizParticipationList
        ];
        return FacadeResponse::json($obj);
    }



    public function getTopUserListBasedOnLectureWatching ($pageSize)
    {
        $list = LogLectureVideo::join('users', 'log_lecture_videos.user_id', 'users.id')
                                ->select(
                                    'users.id',
                                    'users.name',
                                    'users.mobile_number',
                                    'users.email',
                                    'users.gender',
                                    'users.address',
                                    'users.is_e_edu_3',
                                    'users.is_staff'
                                    )
                                    ->where('users.is_staff', false)
                                    // ->where('users.is_e_edu_3', true)
                                    ->groupBy(
                                        'id',
                                        'name',
                                        'mobile_number',
                                        'email',
                                        'gender',
                                        'address',
                                        'is_staff',
                                        'is_e_edu_3'
                                    )
                                ->selectRaw('sum(log_lecture_videos.duration) as totalSpentTimeOnLecture')
                                ->orderBy('totalSpentTimeOnLecture', 'desc')
                                ->limit(5)
                                ->get();
        return FacadeResponse::json($list);
    }


    public function getUserListBasedOnLectureWatchingPaginated ($pageSize, $pageNumber)
    {

        $totalRows = LogLectureVideo::join('users', 'log_lecture_videos.user_id', 'users.id')
                                ->select('users.id')
                                ->groupBy('users.id')
                                ->get();

        $list = LogLectureVideo::join('users', 'log_lecture_videos.user_id', 'users.id')
                                ->select(
                                    'users.id',
                                    'users.name',
                                    'users.mobile_number',
                                    'users.email',
                                    'users.gender',
                                    'users.address'
                                    )
                                    ->groupBy(
                                        'id',
                                        'name',
                                        'mobile_number',
                                        'email',
                                        'gender',
                                        'address'
                                    )
                                ->selectRaw('sum(log_lecture_videos.duration) as totalSpentTimeOnLecture')
                                ->orderBy('totalSpentTimeOnLecture', 'desc')
                                ->limit($pageSize)
                                ->skip($pageSize * $pageNumber)
                                ->get();
            $obj = (Object) [
                "totalRows" =>  count($totalRows),
                "records" =>  $list
            ];
        return FacadeResponse::json($obj);
    }



    public function getDayWiseLectureWatchListByUserId($userId, $courseId)
    {
        // return [];
            $history = LogLectureVideo::join('lecture_videos', 'log_lecture_videos.lecture_id', 'lecture_videos.id')
            ->join('courses', 'log_lecture_videos.course_id', 'courses.id')
            ->join('subjects', 'log_lecture_videos.subject_id', 'subjects.id')
            ->join('chapters', 'log_lecture_videos.chapter_id', 'chapters.id')
            ->where('log_lecture_videos.user_id', $userId)
            ->where('log_lecture_videos.course_id', $courseId)
            // ->where('log_lecture_videos.is_skipped', false)
            ->select(
                //'log_lecture_videos.created_at as day',
                //'lecture_videos.id',
                DB::raw('DATE(log_lecture_videos.created_at) as date')
                )
                ->groupBy(
                    'date'
                )
                ->orderBy('date', 'DESC')

            ->selectRaw('sum(log_lecture_videos.duration) as totalDuration')
            ->get();


            // $item->history = $history;
        // }
        return $history;
    }

    public function getSubjectDayWiseLectureWatchListByUserId($userId, $courseId, $subjectId)
    {
        // return [];
            $history = LogLectureVideo::join('lecture_videos', 'log_lecture_videos.lecture_id', 'lecture_videos.id')
            ->join('courses', 'log_lecture_videos.course_id', 'courses.id')
            ->join('subjects', 'log_lecture_videos.subject_id', 'subjects.id')
            ->join('chapters', 'log_lecture_videos.chapter_id', 'chapters.id')
            ->where('log_lecture_videos.user_id', $userId)
            ->where('log_lecture_videos.course_id', $courseId)
            ->where('log_lecture_videos.subject_id', $subjectId)
            // ->where('log_lecture_videos.is_skipped', false)
            ->select(
                //'log_lecture_videos.created_at as day',
                //'lecture_videos.id',
                DB::raw('DATE(log_lecture_videos.created_at) as date')
                )
                ->groupBy(
                    'date'
                )
                ->orderBy('date', 'DESC')

            ->selectRaw('sum(log_lecture_videos.duration) as totalDuration')
            ->get();


            // $item->history = $history;
        // }
        return $history;
    }

    public function getLectureWatchListByUserId($userId, $courseId)
    {
        // return [];
            $history = LogLectureVideo::join('lecture_videos', 'log_lecture_videos.lecture_id', 'lecture_videos.id')
            ->join('courses', 'log_lecture_videos.course_id', 'courses.id')
            ->join('subjects', 'log_lecture_videos.subject_id', 'subjects.id')
            ->join('chapters', 'log_lecture_videos.chapter_id', 'chapters.id')
            ->where('log_lecture_videos.user_id', $userId)
            ->where('log_lecture_videos.course_id', $courseId)
            // ->where('log_lecture_videos.is_skipped', false)
            ->select(
                'lecture_videos.id',
                'subjects.name as subject_name',
                'chapters.name as chapter_name',
                'lecture_videos.title'
                )
                ->groupBy(
                    'id',
                    'subject_name',
                    'chapter_name',
                    'title'
                )

            ->selectRaw('sum(log_lecture_videos.duration) as totalDuration')
            ->selectRaw('count(log_lecture_videos.id) as totalCount')
            ->get();

            $logScript = new LogScriptController();

            foreach ($history as $hs) {
                // $hs->is_full_watched = false ; // $this->getIsfullWatched($hs->id, $userId);
                // $hs->is_downloaded_script = false; // $logScript->getIsDownloaded($hs->id, $userId);
                $hs->is_full_watched = $this->getIsfullWatched($hs->id, $userId);
                $hs->is_downloaded_script = $logScript->getIsDownloaded($hs->id, $userId);
            }
            // $item->history = $history;
        // }
        return $history;
    }

    private function getIsfullWatched ($lectureId, $userId) {
      $watchTime = LogLectureWatchComplete::where('lecture_id', $lectureId)->where('user_id', $userId)->where('is_full_watched', true)->count();
      return $watchTime ? true : false;
    }

    public function getUserHistory ($userId) {
        $user = User::find($userId);
        $couser_wise_list = LogLectureVideo::join('lecture_videos', 'log_lecture_videos.lecture_id', 'lecture_videos.id')
                                ->join('courses', 'log_lecture_videos.course_id', 'courses.id')
                                ->where('log_lecture_videos.user_id', $userId)
                                ->select(
                                    'courses.id as course_id',
                                    'courses.name as course_name'
                                    )
                                    ->groupBy(
                                        'course_id',
                                        'course_name'
                                    )
                                ->selectRaw('sum(log_lecture_videos.duration) as total_watched_duration')
                                ->selectRaw('count(log_lecture_videos.id) as total_video_seen_count')
                                ->get();

        $resultLecture = new ResultLectureController();
        foreach ($couser_wise_list as $item) {
            $subject_ids = CourseSubject::join('subjects', 'course_subjects.subject_id', 'subjects.id')
            ->where('course_id',$item->course_id)->pluck('course_subjects.subject_id');

            //$subject_wise_array = [];
            $subject_wise_list = LogLectureVideo::join('subjects', 'log_lecture_videos.subject_id', 'subjects.id')
                ->where('log_lecture_videos.course_id', $item->course_id)
                ->whereIn('log_lecture_videos.subject_id', $subject_ids)
                ->where('log_lecture_videos.user_id', $userId)
                ->select(
                    'subjects.id as subject_id',
                    'subjects.name as subject_name'
                    )
                    ->groupBy(
                        'subject_id',
                        'subject_name'
                    )
                ->selectRaw('sum(log_lecture_videos.duration) as total_watched_duration')
                ->selectRaw('count(log_lecture_videos.id) as total_video_seen_count')
                ->get();

                foreach ($subject_wise_list as $data) {
                    $data->subject_day_wise_history = $this->getSubjectDayWiseLectureWatchListByUserId($userId, $item->course_id,$data->subject_id);
                }

            $item->subject_wise = $subject_wise_list;
            $item->day_wise_history = $this->getDayWiseLectureWatchListByUserId($userId, $item->course_id);

        }
        $user->image = $user->image ? 'https://api.bacbonschool.com/uploads/userImages/'.$user->image : null;
        $user->video_details = $couser_wise_list;
        $user->quiz_details = $resultLecture->getCourseQuizHistoryForChart($userId);
        return FacadeResponse::json($user);
    }



    public function getUserShortHistory ($userId) {
        $user = User::find($userId);
        $list = LogLectureVideo::join('lecture_videos', 'log_lecture_videos.lecture_id', 'lecture_videos.id')
                                ->join('courses', 'log_lecture_videos.course_id', 'courses.id')
                                ->where('log_lecture_videos.user_id', $userId)
                                ->select(
                                    'courses.id as course_id',
                                    'courses.name as course_name'
                                    )
                                    ->groupBy(
                                        'course_id',
                                        'course_name'
                                    )
                                ->selectRaw('sum(log_lecture_videos.duration) as total_duration')
                                ->get();

        $resultLecture = new ResultLectureController();
        foreach ($list as $item) {
            $item->participated_quiz_count = $resultLecture->participatedQuizCount($item->course_id, $userId);
            $item->history = $this->getLectureWatchListByUserId($userId, $item->course_id);
            $item->quiz_history = $resultLecture->participatedIndividualQuizCount($item->course_id, $userId);
        }
        $user->image = $user->image ? 'https://api.bacbonschool.com/uploads/userImages/'.$user->image : null;
        $user->short_details = $list;
        $user->quiz_participation_details = $resultLecture->getCourseQuizHistory($userId);
        return FacadeResponse::json($user);
    }


    public function getUserShortHistoryByPhone ($phone) {
        $user = User::where('mobile_number', $phone)->first();
        if (is_null($user)) {
            return FacadeResponse::json($user);
        }
        $userId = $user->id;
        $list = LogLectureVideo::join('lecture_videos', 'log_lecture_videos.lecture_id', 'lecture_videos.id')
                                ->join('courses', 'log_lecture_videos.course_id', 'courses.id')
                                ->where('log_lecture_videos.user_id', $userId)
                                ->select(
                                    'courses.id as course_id',
                                    'courses.name as course_name'
                                    )
                                    ->groupBy(
                                        'course_id',
                                        'course_name'
                                    )
                                ->selectRaw('sum(log_lecture_videos.duration) as total_duration')
                                ->get();

        $resultLecture = new ResultLectureController();
        foreach ($list as $item) {
            $item->participated_quiz_count = $resultLecture->participatedQuizCount($item->course_id, $userId);
            $item->history = $this->getLectureWatchListByUserId($userId, $item->course_id);
            $item->quiz_history = $resultLecture->participatedIndividualQuizCount($item->course_id, $userId);
        }
        $user->image = $user->image ? 'https://api.bacbonschool.com/uploads/userImages/'.$user->image : null;
        $user->short_details = $list;
        $user->quiz_participation_details = $resultLecture->getCourseQuizHistory($userId);
        return FacadeResponse::json($user);
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
    public function storeLectureWatchHistory(Request $request)
    {
        $response = new ResponseObject;
        $data = $request->all();
        try {
            foreach ($data as $item) {
                // return FacadeResponse::json($this->storeFullWatchedLog($item['user_id'], $item['lecture_id']));
                $isSubmitted = LogLectureVideo::where('user_id', $item['user_id'])
                ->where('start_time', date('Y-m-d H:i:s', strtotime($item['start_time'])))->count();
                if (!$isSubmitted) {
                    LogLectureVideo::create([
                        'course_id' => $item['course_id'],
                        'subject_id' => $item['subject_id'],
                        'chapter_id' => $item['chapter_id'],
                        'lecture_id' => $item['lecture_id'],
                        'user_id' => $item['user_id'],
                        'duration' => $item['duration'],
                        'start_position' => $item['start_position'],
                        'end_position' => $item['end_position'],
                        'start_time' => date('Y-m-d H:i:s', strtotime($item['start_time'])),
                        'end_time' =>date('Y-m-d H:i:s', strtotime($item['end_time'])),
                        'is_skipped' => $item['is_skipped']
                    ]);
                    $this->storeFullWatchedLog($item['user_id'], $item['lecture_id']);
                }
            }
            $response->status = $response::status_ok;
            $response->messages = "Thank you for watching";

        } catch (Exception $e) {

            $response->status = $response::status_fail;
            $response->messages = $e->getMessage();

        }
        return FacadeResponse::json($response);
    }


    public function storeFullWatchedLog ($userId, $lectureId) {
        $lecture = LectureVideo::where('id', $lectureId)->first();

        $lastSeen = LogLectureWatchComplete::where('user_id', $userId)->where('lecture_id', $lectureId)
        ->where('is_full_watched', true)
        ->orderBy('id', 'desc')
        ->first();
        $lastSeenId = !is_null($lastSeen) ? $lastSeen->last_watched_log_id : null;

        $allWatchLogs  = LogLectureVideo::where('user_id', $userId)->where('lecture_id', $lectureId)
        ->when($lastSeenId, function ($query) use ($lastSeenId){
            return $query->where('id', '>=', $lastSeenId + 1);
        })
        ->get();

        $watchedPositions = [];
        foreach ($allWatchLogs as $watchLog) {

            for($i = $watchLog->start_position; $i <= $watchLog->end_position; $i++) {
                if (!in_array($i, $watchedPositions)) {
                    $watchedPositions[] = $i;
                }
            }
        }
        $isSaved = LogLectureWatchComplete::where('user_id', $userId)
        ->where('lecture_id', $lectureId)
        ->where('is_full_watched', false)
        ->where('id', '>=', $lastSeenId+1)
        ->orderBy('id', 'desc')
        ->first();
        $watchedPercent = (count($watchedPositions) -1) * 100 / $lecture->duration;
        if (is_null($isSaved)) {
           $isSaved = LogLectureWatchComplete::create([
                'user_id' => $userId,
                'course_id' => $lecture->course_id,
                'subject_id' => $lecture->subject_id,
                'chapter_id' => $lecture->chapter_id,
                'lecture_id' => $lectureId,
                'last_watched_log_id' => $allWatchLogs[count($allWatchLogs)-1]->id,
                'total_duration' => $lecture->duration,
                'watch_duration' => count($watchedPositions) -1,
                'is_full_watched' => $watchedPercent > 80 ? true : false
            ]);

        } else {
            $isSaved->update([
                'last_watched_log_id' => $allWatchLogs[count($allWatchLogs)-1]->id,
                'watch_duration' => count($watchedPositions) -1,
                'is_full_watched' => $watchedPercent > 80 ? true : false
            ]);
        }
        return $isSaved;

    }





    public function getEEucationPhase3StudyHistory()
    {
        $students = User::leftJoin('log_lecture_videos', 'log_lecture_videos.user_id', 'users.id')
        ->where('users.is_e_edu_3', true)
        ->select('users.id', 'users.name', 'users.mobile_number', 'users.gender', 'users.email')
        ->groupBy(
            'id',
            'name',
            'mobile_number',
            'gender',
            'email'
        )
        // ->whereIn('log_lecture_videos.course_id', [2, 13, 15])
        ->selectRaw('sum(log_lecture_videos.duration) as total_duration')
        ->with('quizResults')
        ->get();

        $user_array = array(["#", "Name", "Phone", "Email", "Gender", "Total Watched Time", "Quiz Name", "Course", "Marks", "Total Mark"],
            ["", "", "", "", "", "", "", "", ""]
            );
            $count = 1;
            foreach ($students as $student) {
                $user_array[] = array(
                    $count++ . '.',
                    $student->name,
                    $student->mobile_number,
                    $student->email,
                    $student->gender,
                    $student->total_duration,
                    "",
                    "",
                    "",
                    ""
                    );
                foreach ($student->quizResults as $quiz) {
                    $user_array[] = array(
                        "",
                        "",
                        "",
                        "",
                        "",
                        "",
                        $quiz->exam_name,
                        $quiz->name,
                        $quiz->mark,
                        $quiz->total_mark

                        );
                }
            }

        $export = new UserExport($user_array);
        $time = date("Y-m-d h:i:s");
        return Excel::download($export, 'eEducation Phase 03 Study History'.$time.'users.xlsx');
    }


    public function getGoalStatus(Request $request)
    {
        $userId = $request->userId;

        $daily_goal = (Object) [
            "video_seen_required" => 2,
            "video_seen" => LogLectureWatchComplete::where('is_full_watched', true)->where('user_id', $userId)->whereDate('updated_at', Carbon::today())->count(),
            "quiz_participated_required" => 2,
            "quiz_participated" => ResultLecture::where('user_id', $userId)->whereDate('created_at', Carbon::today())->count()
        ];

        $weekly_goal = (Object) [
            "video_seen_required" => 10,
            "video_seen" => LogLectureWatchComplete::where('is_full_watched', true)->where('user_id', $userId)->whereBetween('updated_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count(),
            "quiz_participated_required" => 10,
            "quiz_participated" => ResultLecture::where('user_id', $userId)->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count()
        ];

        $monthly_goal = (Object) [
            "video_seen_required" => 25,
            "video_seen" => LogLectureWatchComplete::where('is_full_watched', true)->where('user_id', $userId)->whereYear('updated_at', Carbon::now()->year)->whereMonth('updated_at', Carbon::now()->month)->count(),
            "quiz_participated_required" => 25,
            "quiz_participated" => ResultLecture::where('user_id', $userId)->whereYear('created_at', Carbon::now()->year)->whereMonth('created_at', Carbon::now()->month)->count()
        ];

        $goal = (Object) [
            "notifications" => $this->getNotifications($userId),
            "daily_goal" => $daily_goal,
            "weekly_goal" => $weekly_goal,
            "monthly_goal" => $monthly_goal
        ];

        return FacadeResponse::json($goal);
    }

    private function getNotifications ($userId) {
        $givenExams = ResultReview::where('user_id', $userId)->select('review_exam_id')->groupBy('review_exam_id')->get();
        $givenExamIds = [];
        foreach ($givenExams as $xm) {
            $givenExamIds[] = $xm->review_exam_id;
        }
        $exams = ReviewExam::where('user_id', $userId)->whereNotIn('id', $givenExamIds)->get();
        $notifications = [];
        foreach ($exams as $exam) {
            $notifications[] = $exam->exam_name . " generated, Please complete";
        }
        return $notifications;
    }


    public function cleanLogLectureWatchComplete () {
        $data = LogLectureWatchComplete::select('user_id', 'lecture_id', DB::raw('count(*) as total'))
                                        ->where('is_full_watched', true)
                                        ->groupBy('user_id', 'lecture_id')
                                        ->orderBy('total', 'desc')
                                        ->get();
        $count = 0;
        foreach ($data as $item) {
            $log = LogLectureWatchComplete::where('user_id', $item->user_id)
                                            ->where('lecture_id', $item->lecture_id)
                                            ->first();
            $deleted = LogLectureWatchComplete::where('user_id', $item->user_id)
                        ->where('lecture_id', $item->lecture_id)
                        ->where('id', '!=', $log->id)
                        ->delete();
            $count++;
        }
        return FacadeResponse::json($count);
    }
}
