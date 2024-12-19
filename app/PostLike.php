<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostLike extends Model
{
    public $table = "postlikes";
    protected $fillable = ['id','user_id','post_id'];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class,'post_id');
    }
}
