<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class blog_article extends Model
{
    protected $fillable = [
        "user_id",
        "name",
        "email",
        "category_id",
        "titile",
        "description",
        "image",
        "status",
        "is_boosted",
        "published_at"
    ];

    public function category () {
        return $this->belongsTo('App\blog_category', 'category_id' ,'id');
    }
    
    public function comments () {
        return $this->hasMany('App\BlogComment', 'article_id' ,'id')->orderBy('id', 'desc');
    }
    
}
