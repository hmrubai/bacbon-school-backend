<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SchoolAssistance extends Model
{
    protected $fillable = ["institute_name", "medium", "level", "phone_number", "email", "admission_procedure_url",
    "admission_procedure_text", "admission_requirments", "contact_address", "institute_url", "admission_url"];
}

