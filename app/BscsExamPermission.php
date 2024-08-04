<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BscsExamPermission extends Model
{
    protected $fillable = [
        'bscs_exam_id',
        'user_id',
        'mcq_permission_count',
        'written_permission_count'

    ];

}
