<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BscsWrittenExam extends Model
{
    protected $fillable = [
        'bscs_exam_id',
        'question',
        'duration'
    ];

}
