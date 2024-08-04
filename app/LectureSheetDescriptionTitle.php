<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LectureSheetDescriptionTitle extends Model
{
    protected $table = 'lecture_sheet_description_titles';

    protected $fillable = ['name', 'lecture_sheet_id'];

    public function lecture_sheet()
    {
        return $this->belongsTo('App\LectureSheet', 'lecture_sheet_id', 'id');
    }

    public function lecture_sheet_description_detial (){
        return $this->hasMany('App\LectureSheetDescriptionDetail');
    }
  
}
