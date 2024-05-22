<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CommentLike extends Model
{
    public $table = "commentlikes";
    protected $fillable = ['id','user_id','post_id','comment_id'];
}
