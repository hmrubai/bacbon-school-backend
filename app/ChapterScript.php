<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ChapterScript extends Model
{
    protected $fillable = [
        'course_id',
        'subject_id',
        'chapter_id',
        'title', 'title_bn', 'title_jp',
        'url',
        'status',
        'is_premium',
        'price',
        'price_text',
        'sequence'
];

    protected $casts = [
        'price' => 'double',
        'is_premium' => 'boolean'
    ];
}
