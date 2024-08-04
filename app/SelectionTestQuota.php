<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class SelectionTestQuota extends Model
{
    protected $fillable = [
        'user_id',
        'selection_test_id',
        'quota',
        'consumption',
        'participation_date'
    ];
}
