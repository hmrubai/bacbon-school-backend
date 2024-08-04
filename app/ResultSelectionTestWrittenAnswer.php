<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ResultSelectionTestWrittenAnswer extends Model
{
    protected $fillable = [
        'user_id',
        'selection_test_id',
        'selection_test_written_question_id',
        'answer'
    ];
}
