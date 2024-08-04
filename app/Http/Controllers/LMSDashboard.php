<?php

namespace App\Http\Controllers;

use \Illuminate\Support\Facades\Response as FacadeResponse;
use Illuminate\Http\Request;

use App\User;
use App\eBook;
use App\LectureScript;
use App\ChapterScript;
use App\LectureVideo;
use App\SubjectExam;
use App\LectureExam;
use App\ChapterExam;

class LMSDashboard extends Controller
{    
    public function getItemCount () {
        $obj = (Object) [
            "totalUser" =>  User::count(),
            "totalLecture" =>  LectureVideo::count(),
            "totalScript" =>  LectureScript::count() + ChapterScript::count(),
            "totalExam" =>  LectureExam::count() + ChapterExam::count() + SubjectExam::count(),
            "totalAudioBook" =>  LectureVideo::whereNotNull('audio_book')->orWhereNotNull('audio_book_aws')->count(),
            "totalEBook" =>  eBook::count()
        ];
        return FacadeResponse::json($obj);
    }

}
