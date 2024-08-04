<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BscsWrittenExamAnswer extends Model
{
    protected $fillable = [
        'user_id',
        'bscs_written_exam_id',
        'answer',
        'start_time',
        'end_time',
        'status',
    ];
}
