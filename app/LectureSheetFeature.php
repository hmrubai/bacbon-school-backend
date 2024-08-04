<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LectureSheetFeature extends Model
{
    protected $fillable = ['name', 'lecture_sheet_id'];

    public function lecture_sheet()
    {
        return $this->belongsTo('App\LectureSheet', 'lecture_sheet_id', 'id');
    }
  
}
