<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    public $table = "comments";
    protected $fillable = ['id','user_id','post_id','comment_id','comment_text','type','rating'];

    public function ratings()
    {
        return $this->hasMany(CommentRating::class);
    }

    public function averageRating()
    {
        return $this->ratings()->avg('rating');
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
    public function reply()
    {
        return $this->hasMany(Comment::class,'comment_id','id');
    }
    public function comment()
    {
        return $this->hasMany(Comment::class,'post_id','id')->whereNull('comment_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function commentlike()
    {
        return $this->hasMany(CommentLike::class,'comment_id','id');
    }
    public function replylike()
    {
        return $this->hasMany(CommentLike::class,'comment_id','id');
    }
    public function getRatingAttribute($val)
    {
        if(!empty($val) || $val > 0){
            return number_format((float)$val, 1, '.', '');
        }
        else{
            return "0";
        }

    }
}
