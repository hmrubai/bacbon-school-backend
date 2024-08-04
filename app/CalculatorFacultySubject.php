<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CalculatorFacultySubject extends Model
{
    protected $fillable = ["university_id", "unit_id", "faculty", "faculty_bn", "subject_name", "subject_name_bn"];
}



