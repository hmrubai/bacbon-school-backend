<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LogEBook extends Model
{
    protected $fillable = ['course_id', 'subject_id', 'e_book_id', 'user_id', 'start_time', 'end_time'];
}
