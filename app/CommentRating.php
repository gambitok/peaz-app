<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CommentRating extends Model
{
    protected $fillable = ['comment_id', 'user_id', 'rating'];

    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
