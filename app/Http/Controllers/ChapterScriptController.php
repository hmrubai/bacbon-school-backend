<?php

namespace App\Http\Controllers;

use \Illuminate\Http\Response;
use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;
use Illuminate\Http\Request;
use Validator;
use App\ChapterScript;
use App\Chapter;

class ChapterScriptController extends Controller
{
    public function getScriptlistByChapterId($id) {
        $response = new ResponseObject;
        $scriptlist = ChapterScript::where('chapter_id', $id)->get();
        return FacadeResponse::json($scriptlist);
    }

    public function storeChapterScript(Request $request) {

        $response = new ResponseObject;

        $formData = json_decode($request->data, true);
        // $data = $request->json()->all();

        $request['status'] = "Available";
        $validator = Validator::make($formData, [
            'subject_id' => 'required',
            'chapter_id' => 'required',
            'title' => 'required',
            'course_id' => 'required'
        ]);
        if ($validator->fails()) {
            $response->status = $response::status_fail;
            $response->messages = $validator->errors();
            // $response->messages = "Validation failed";
            return FacadeResponse::json($response);
        }
        if ($request->hasFile('file') ) {

            $file = $request->file('file');
            $formData['filename'] = "CH".time().'.'.$file->getClientOriginalExtension();
            $formData['status'] = "Available";
            // $destinationPath = 'uploads/Chapters';
            $destinationPath = 'uploads/Lectures';
            $file->move($destinationPath,$formData['filename']);

            $script = ChapterScript::create([
                'course_id' =>$formData['course_id'],
                'subject_id' =>$formData['subject_id'],
                'chapter_id' => $formData['chapter_id'],
                'title' => $formData['title'],
                'status' => $formData['status'],
                // 'url' => $_SERVER['HTTP_HOST'].'/'.$destinationPath.'/'.$formData['filename']
                'url' => $formData['filename']
            ]);

            $response->status = $response::status_ok;
            $response->messages = "Successfully uploaded";
            $response->result = $script;
            return FacadeResponse::json($response);
        } else {
            $response->status = $response::status_fail;
            $response->messages = "Please select script";
            return FacadeResponse::json($response);
        }


    }
    public function deleteChapterScript (Request $request) {
        $response = new ResponseObject;
        $chapterScript = ChapterScript::find($request->id);
        if ($chapterScript) {

            if(file_exists('uploads/Chapters/'.$chapterScript->url)){
                unlink('uploads/Chapters/'.$chapterScript->url);
            }
            if(file_exists('uploads/Lectures/'.$chapterScript->url)){
                unlink('uploads/Lectures/'.$chapterScript->url);
            }
        }
        $deleted = $chapterScript->delete();
        $response->status = $response::status_ok;
        $response->messages = "Successfully deleted";
        $response->result = $deleted;
        return FacadeResponse::json($response);
    }

    public function renameChapterScript (Request $request) {
        $chapters = Chapter::where('course_id', $request->course_id)->where('subject_id', $request->subject_id)->get();
        $count = 0;
        foreach ($chapters as $chapter) {
            $scripts = ChapterScript::where('chapter_id', $chapter->id)->get();
            foreach ($scripts as $script) {
                $count++;
                $script->update([
                    'title' => $chapter->name .' '. $script->title,
                    'title_bn' => $chapter->name .' '. $script->title
                ]);
            }
        }

        $response = new ResponseObject;
        $response->status = $response::status_ok;
        $response->messages = "Successfully updated";
        $response->result = $count;
        return FacadeResponse::json($response);
    }
}
