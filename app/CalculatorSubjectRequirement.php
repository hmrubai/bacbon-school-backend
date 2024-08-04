<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CalculatorSubjectRequirement extends Model
{
    protected $fillable = ["faculty_subject_id", "subject", "subject_bn", "required_point"];
}
