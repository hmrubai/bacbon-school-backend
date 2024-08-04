<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BlogComment extends Model
{
    protected $fillable = ['article_id', 'name', 'email', 'comment', 'is_approved', 'status'];
}
