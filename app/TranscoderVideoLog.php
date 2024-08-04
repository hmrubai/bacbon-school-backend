<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TranscoderVideoLog extends Model
{
    protected $fillable = ['url', 'full_url', 'download_url', 'lecture_video_id'];

    public static $rules = [];
}
