<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LectureSheetDescriptionDetail extends Model
{
    protected $table = 'lecture_sheet_description_details';

    protected $fillable = ['name', 'lecture_sheet_description_title_id'];

    public function lecture_sheet_description_title()
    {
        return $this->belongsTo('App\LectureSheetDescriptionTitle', 'lecture_sheet_description_title_id', 'id');
    }
  
}
