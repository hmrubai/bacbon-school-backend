<?php

namespace App\Http\Controllers;
use \Illuminate\Support\Facades\Response as FacadeResponse;

use App\User;
use App\BscsExam;
use App\BscsExamQuestion;
use App\BscsExamPermission;
use App\BscsResults;
use App\BscsStatus;
use App\BscsWrittenExam;
use App\BscsWrittenExamAnswer;
use Illuminate\Http\Request;

class BscsExamController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getBscsExamList(Request $request)
    {

        $userId = $request->userId;

        $examList = BscsExam::where('is_active', true)->whereDate('appeared_from', '<=', date("Y-m-d"))->whereDate('appeared_to', '>=', date("Y-m-d"))->get();
        $list = [];
        foreach ($examList as $exam) {
            $result_count = BscsResults::where('bscs_exam_id',$exam->id)->where('user_id',$userId)->count();

            $result_count_written = BscsWrittenExam::where('bscs_written_exams.bscs_exam_id',$exam->id)
                                        ->where('bscs_written_exam_answers.user_id',$userId)
                                        ->where('bscs_written_exam_answers.status', 'submitted')
                                        ->join('bscs_written_exam_answers','bscs_written_exams.id','bscs_written_exam_answers.bscs_written_exam_id')
                                        ->count();

            $permission = BscsExamPermission::where('bscs_exam_id',$exam->id)->where('user_id',$userId)->first();
            $exam->permission = $permission ? $permission->mcq_permission_count : 1;
            $exam->perticipated_count = $result_count;
            $exam->details_url = "api/getBscsExamQuestionsById/". $exam->id;

            $written_exam = BscsWrittenExam::where('bscs_exam_id',$exam->id)->first();
            if (!is_null($written_exam)) {
                $written_answer = BscsWrittenExamAnswer::where('bscs_written_exam_id', $written_exam->id)
                ->where('user_id', $userId)
                ->orderBy('id', 'desc')
                ->first();
                // $seconds = 0;
                // if ($written_answer) {

                    // $current = date("Y-m-d H:i:s");
                    // $currentInSec = strtotime($current);
                    // $startTimeInSec = strtotime($written_answer->start_time);
                    // $newdate = $startTimeInSec + ($written_exam->duration * 60);

                    // $seconds = $newdate - $currentInSec ;
                // }

                // $written_exam->left_duration = $seconds;
                // $written_exam->answer = $written_answer ? $written_answer->answer : null;
                $written_exam->status = $written_answer ? $written_answer->status : 'not_perticipated';
                $written_exam->permission = $permission ? $permission->written_permission_count : 1;
                $written_exam->perticipated_count = $result_count_written;
            }

            $obj = (Object) [
                "mcq" => $exam,
                "written" => $written_exam
            ];
            $list[] = $obj;
        }


        return FacadeResponse::json($list);
    }




    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getBscsExamListV2(Request $request)
    {

        $userId = $request->userId;
        $examList = BscsExam::where('is_active', true)->whereDate('appeared_from', '<=', date("Y-m-d"))->whereDate('appeared_to', '>=', date("Y-m-d"))->get();
        $list = [];
        foreach ($examList as $exam) {
            $result_count = BscsResults::where('bscs_exam_id',$exam->id)->where('user_id',$userId)->count();

            $result_count_written = BscsWrittenExam::where('bscs_written_exams.bscs_exam_id',$exam->id)
                                        ->where('bscs_written_exam_answers.user_id',$userId)
                                        ->where('bscs_written_exam_answers.status', 'submitted')
                                        ->join('bscs_written_exam_answers','bscs_written_exams.id','bscs_written_exam_answers.bscs_written_exam_id')
                                        ->count();

            $permission = BscsExamPermission::where('bscs_exam_id',$exam->id)->where('user_id',$userId)->first();
            $exam->permission = $permission ? $permission->mcq_permission_count : 1;
            $exam->perticipated_count = $result_count;
            $exam->details_url = "api/getBscsExamQuestionsById/". $exam->id;

            $written_exam = BscsWrittenExam::where('bscs_exam_id',$exam->id)->first();
            if (!is_null($written_exam)) {
                $written_answer = BscsWrittenExamAnswer::where('bscs_written_exam_id', $written_exam->id)
                ->where('user_id', $userId)
                ->orderBy('id', 'desc')
                ->first();
                $written_exam->status = $written_answer ? $written_answer->status : 'not_perticipated';
                $written_exam->permission = $permission ? $permission->written_permission_count : 1;
                $written_exam->perticipated_count = $result_count_written;
            }

            $obj = (Object) [
                "mcq" => $exam,
                "written" => $written_exam
            ];
            $list[] = $obj;
        }

        $bscsStatus = BscsStatus::first();
        $data = (Object) [
            "is_active" => $bscsStatus ? $bscsStatus->is_active : false,
            "data" => $list
        ];

        return FacadeResponse::json($data);
    }



    public function getBscsExamQuestionsById($examId)
    {
        $exam = BscsExam::where('id',$examId)->first();
            $questions = BscsExamQuestion::inRandomOrder(time())
            ->limit($exam->question_number)
            ->get();
        $obj = (Object) [
            "data" => $questions,
            "submission_url" => "api/submitBscsExamResult"
        ];
        return FacadeResponse::json($obj);
    }

    public function getWrittenExamDetails (Request $request) {
        return FacadeResponse::json($this->getWrittenDetailsRaw($request->writtenExamId, $request->userId));
    }

    public function getWrittenDetailsRaw ($examId, $userId) {

        $written_exam = BscsWrittenExam::where('id',$examId)->first();
        $written_answer = BscsWrittenExamAnswer::where('bscs_written_exam_id', $written_exam->id)
        ->where('user_id', $userId)
        ->orderBy('id', 'desc')
        ->first();
        $seconds = 0;
        if ($written_answer) {

            $current = date("Y-m-d H:i:s");
            $currentInSec = strtotime($current);
            $startTimeInSec = strtotime($written_answer->start_time);
            $newdate = $startTimeInSec + ($written_exam->duration * 60);

            $seconds = $newdate - $currentInSec ;
        }


        $written_exam->left_duration = $seconds;
        $written_exam->answer = $written_answer ? $written_answer->answer : null;

        if($written_exam->left_duration < -300){
            BscsWrittenExamAnswer::where('id', $written_answer->id)->update([
            "status" =>"expired"
            ]);

            $written_answer = BscsWrittenExamAnswer::where('id', '=',  $written_answer->id)->first();
        }

        $written_exam->status = $written_answer ? $written_answer->status : 'not_perticipated';

        return $written_exam;
    }
}
