<?php

namespace App\Exports;

use App\PaidCourseQuizQuestion;
use App\PaidCourseMaterial;
use App\User;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\ResultPaidCouresQuizAnswer;
use Maatwebsite\Excel\Concerns\FromCollection;
use App\ResultPaidCouresQuiz;


class StudentExamResultExport implements FromArray, WithHeadings
{
    private $paid_course_material_id;

    public function __construct($paid_course_material_id)
    {
        $this->paid_course_material_id = $paid_course_material_id;
    }

    public function headings(): array
    {
        $questions = PaidCourseQuizQuestion::orderBy('id', 'ASC')->where('paid_course_material_id', $this->paid_course_material_id)->pluck('question')->toArray();

        if (count($questions) > 100) {
            throw new \Exception('Too many columns. Excel supports up to 16,384 columns (XFD).');
        }

        return array_merge(['Student Name', 'Phone No'], $questions);
    }

    public function array(): array
    {
        // Get the result details
        $resultPaidCourseQuiz = ResultPaidCouresQuiz::where('paid_course_material_id', $this->paid_course_material_id)->get();
        $questions = PaidCourseQuizQuestion::orderBy('id', 'ASC')->where('paid_course_material_id', $this->paid_course_material_id)->get();

        $data = [];

        foreach ($resultPaidCourseQuiz as $item) {
            $user = User::where('id', $item->user_id)->first();
            $answers = ResultPaidCouresQuizAnswer::where('result_paid_coures_quiz_id', $item->id)->get();
            
            //$data = [];
            $row = [
                'name' => $user->name,
                'phone' => $user->mobile_number,
            ];

            foreach ($questions as $index => $question) {
                $answer = $this->getAnswerText($answers, $index + 1, $question);
                $row["question_{$index}"] = $answer;
            }

            $data[] = $row;

            $answers = [];
            $user = [];
        }
        
        return $data;
    }

    private function getAnswerText($answers, $questionIndex, $question)
    {
        $answerRecord = $answers->where('paid_course_quiz_question_id', $question->id)->first();

        $result_alphabet = 'Skipped';

        if (!$answerRecord) {
            $result_alphabet = 'Skipped';
        }

        $answer_value_1 = $answerRecord['answer'];
        $answer_value_2 = $answerRecord['answer2'];
        $answer_value_3 = $answerRecord['answer3'];
        $answer_value_4 = $answerRecord['answer4'];
    
        if ($answer_value_1 == 1){ $result_alphabet = 'A'; };
        if ($answer_value_2 == 2){ $result_alphabet = 'B'; };
        if ($answer_value_3 == 3){ $result_alphabet = 'C'; };
        if ($answer_value_4 == 4){ $result_alphabet = 'D'; };

        return $result_alphabet ? $result_alphabet : 'Skipped';
    }
}
