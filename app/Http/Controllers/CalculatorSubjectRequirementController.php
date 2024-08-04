<?php

namespace App\Http\Controllers;
use \Illuminate\Support\Facades\Response as FacadeResponse;
use App\Http\Helper\ResponseObject;

use App\CalculatorUniversity;
use App\CalculatorUnit;
use App\CalculatorFacultySubject;
use App\CalculatorSubjectRequirement;
use Illuminate\Http\Request;

class CalculatorSubjectRequirementController extends Controller
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
    public function calculateResult(Request $request)
    {
        $response = new ResponseObject;
        $ssc = $request->ssc;
        $hsc = $request->hsc;
        $totalPoints = $hsc['grade_points'] + $ssc['grade_points'];
     
  
        $universities = CalculatorUniversity::where('id', '!=', 2)
        ->with(['units' => function($q) use ($hsc, $totalPoints){
            $q->where('hsc_ssc_point', '<=', $totalPoints)
            ->where(function ($query) use ($hsc) {
                $query->where('group', $hsc['group'])->orWhere('group', null);
            });
        }])
        ->get();
        $indexes = [];
        foreach ($universities as $index=>$university) {
            if (count($university->units)) {
                $indexes[] = $index;
                $units = $university->units;
                foreach ($units as $key=>$unit ) {
                    $subjects = CalculatorFacultySubject::where('unit_id', $unit->id)->get();
                    $available_subjects = [];
                    $unavailable_subjects = [];
                    if (!count($subjects) ) {
                        unset($university->units[$key]);
                    } else {
                        foreach ($subjects as $subject) {
                            $requirements = CalculatorSubjectRequirement::where('faculty_subject_id', $subject->id)->get();
                           
                            if ($this->compareResultWithRequirement($requirements, $hsc['subject_points'])) {
                                $available_subjects[] = $subject;
                            } else {
                                $subject->requirements = $requirements;
                                $unavailable_subjects[] = $subject;
                            }
                        }
                        // $unit->subjects = $subjects;
                        $unit->available_subjects = $available_subjects;
                        $unit->unavailable_subjects = $unavailable_subjects;
                    }

                }
            } else {
                unset($universities[$index]);
            }
        }
        $universityList = [];

        if ($hsc['group'] == 'science') {
            // $this->checkForBuet($hsc);

            $totalHscPoint = 0;
            foreach ($hsc['subject_points'] as $subPoint) {
                 if ($subPoint['subject'] == "Bangla" ||
                 $subPoint['subject'] == "English"
                 ) {
                     $totalHscPoint += $subPoint['grade_points'];
                 } else if ($subPoint['subject'] == "Physics" && $subPoint['grade_points'] >= 4.5) {
                    $totalHscPoint += $subPoint['grade_points'];
                 } else if ($subPoint['subject'] == "Chemistry" && $subPoint['grade_points'] >= 4.5) {
                    $totalHscPoint += $subPoint['grade_points'];
                 } else if ($subPoint['subject'] == "Mathematics" && $subPoint['grade_points'] >= 4.5) {
                    $totalHscPoint += $subPoint['grade_points'];
                 }
            }
            if ($totalHscPoint >= 22.5) {

                $buet = CalculatorUniversity::where('id', 2)->with('units')->first();
                $buet->units[0]->available_subjects = CalculatorFacultySubject::where('unit_id', $buet->units[0]->id)->get();
                $buet->units[0]->unavailable_subjects = [];
                $universityList[0] = $buet;
            }


        }

        foreach ($universities as $index=>$university) {
            if (count($university->units)) {
                $universityList[] = $university;
            } 
        }
        $response->status = $response::status_ok;
        $response->messages = "You are welcome in these universities";
        $response->result = $universityList;

        return FacadeResponse::json($response);
        
    }

    private function compareResultWithRequirement ($requirements, $results) {
        foreach ($requirements as $req) {
            foreach ($results as $res) {
                if ($req->subject === $res['subject']) {
                    if ($req->required_point > $res['grade_points']) return false;
                }
            }
        }
        return true;
    }

    private function checkForBuet ($hsc) {
            $totalHscPoint = 0;
            foreach ($hsc['subject_points'] as $subPoint) {
                 if ($subPoint['subject'] == "Bangla" ||
                 $subPoint['subject'] == "English" || 
                 $subPoint['subject'] == "Physics" ||
                 $subPoint['subject'] == "Chemistry" ||
                 $subPoint['subject'] == "Mathematics"
                 ) {
                     $totalHscPoint += $subPoint['grade_points'];
                 }
            }
            
        
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\CalculatorSubjectRequirement  $calculatorSubjectRequirement
     * @return \Illuminate\Http\Response
     */
    public function show(CalculatorSubjectRequirement $calculatorSubjectRequirement)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\CalculatorSubjectRequirement  $calculatorSubjectRequirement
     * @return \Illuminate\Http\Response
     */
    public function edit(CalculatorSubjectRequirement $calculatorSubjectRequirement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CalculatorSubjectRequirement  $calculatorSubjectRequirement
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CalculatorSubjectRequirement $calculatorSubjectRequirement)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\CalculatorSubjectRequirement  $calculatorSubjectRequirement
     * @return \Illuminate\Http\Response
     */
    public function destroy(CalculatorSubjectRequirement $calculatorSubjectRequirement)
    {
        //
    }
}
