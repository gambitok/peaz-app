<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Postlike extends Model
{
    public $table = "postlikes";
    protected $fillable = ['id','user_id','post_id'];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
