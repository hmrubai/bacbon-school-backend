<?php

namespace App\Http\Controllers;

use App\ScriptText;
use App\LectureScript;
use Illuminate\Http\Request;

class ScriptTextController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
    public function storeLectureScriptText(Request $request)
    {
        $lectureScript = LectureScript::create([
            'title'=> $request->title,
            'subject_id'=> $request->subject_id,
            'chapter_id'=> $request->chapter_id,
            'lecture_id'=> $request->lecture_id,
            'status'=> "Available"
        ]);
        foreach ($request->texts as $req) {
            $fileName = '';
            if ($req['image']) {

                $destinationPath = 'uploads/lecture_imgs/';
                $file = base64_decode($req['image']);
                $firstChar = substr($req['image'], 0, 1);
                $ext = '';
                switch ($firstChar) {
                    case '/':
                        $ext = 'jpg';
                        break;

                    case 'i':
                        $ext = 'png';
                        break;

                    case 'R':
                        $ext = 'gif';
                        break;

                    case 'U':
                        $ext = 'webp';
                        break;
                }
                $fileName = "LSImg" . time() . '.' . $ext;
                $original = file_put_contents($destinationPath . $fileName, $file);
            }

            $textInsertion = ScriptText::create([
                    'lecture_script_id' => $lectureScript->id,
                    'title' => $req['title'],
                    'image' => $fileName,
                    'description' => $req['description']
                ]);


        }
        return 'Done';
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ScriptText  $scriptText
     * @return \Illuminate\Http\Response
     */
    public function show(ScriptText $scriptText)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ScriptText  $scriptText
     * @return \Illuminate\Http\Response
     */
    public function edit(ScriptText $scriptText)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ScriptText  $scriptText
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ScriptText $scriptText)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ScriptText  $scriptText
     * @return \Illuminate\Http\Response
     */
    public function destroy(ScriptText $scriptText)
    {
        //
    }
}
