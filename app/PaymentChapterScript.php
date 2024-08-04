<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentChapterScript extends Model
{
    protected $table = "payment_chapter_script";
    protected $fillable = ["user_id", "chapter_script_id", "amount", "is_complete"];
}
