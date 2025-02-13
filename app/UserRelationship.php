<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserRelationship extends Model
{

//“Following” is the term for the users who you follow. "Followers" are the users who follow you
    protected $fillable = [
        'follower_id',
        'following_id',
    ];

    public function follower()
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    public function following()
    {
        return $this->belongsTo(User::class, 'following_id');
    }
}
