<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Career extends Model
{
    protected $fillable = ["user_id", "job_id", "job_title", "university_id", "name", "phone", "email","university_name", "cover_letter", "work_experience", "work_experience_duration_type", "expected_salary", "file"];
}

