<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SelectionTestWrittenQuestion extends Model
{
    protected $fillable = [
        'selection_test_id',
        'question',
        'mark',
        'question_type',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}


