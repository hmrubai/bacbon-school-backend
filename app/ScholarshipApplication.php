<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ScholarshipApplication extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'date_of_birth',
        'sex',
        'address',
        'country',
        'division_id',
        'district_id',
        'thana',
        'postal_code',
        'email',
        'phone',
        'fax',
        'first_language',
        'other_language',
        'father_name',
        'father_occupation',
        'father_yearly_income',
        'father_contact_no',
        'mother_name',
        'mother_occupation',
        'mother_yearly_income',
        'mother_contact_no',
        'reference_1_name',
        'reference_1_occupation',
        'reference_1_relation',
        'reference_1_contact_no',
        'reference_1_address',
        'reference_2_name',
        'reference_2_occupation',
        'reference_2_relation',
        'reference_2_contact_no',
        'reference_2_address',
        'message'
    ];

    public static $rules = [
        "user_id" => "required",
        // "name" => "max:150",
        // "phone" => "max:15",
        // "fax" => "max:15",
        // "email" => "max:50",
        // "sex" => "max:10",
        // "address" => "max:200",
        // "country" => "max:100|min:4",
        // "postal_code" => "max:100",
        // "first_language" => "max:100",
        // "other_language" => "max:100",
        // "father_name" => "max:150|min:4",
        // "father_occupation" => "max:100",
        // "father_yearly_income" => "numeric",
        // "father_contact_no" => "max:100",
        // "mother_name" => "max:100|min:4",
        // "mother_occupation" => "max:100",
        // "mother_yearly_income" => "numeric",
        // "mother_contact_no" => "max:100",
        // "reference_1_name" => "max:100",
        // "reference_1_occupation" => "max:100",
        // "reference_1_relation" => "max:100",
        // "reference_1_contact_no" => "max:100",
        // "reference_1_address" => "max:200",
        // "reference_2_name" => "max:100",
        // "reference_2_occupation" => "max:100",
        // "reference_2_relation" => "max:100",
        // "reference_2_contact_no" => "max:100",
        // "reference_2_address" => "max:200",
        // "message" => "max:100"
    ];

}
