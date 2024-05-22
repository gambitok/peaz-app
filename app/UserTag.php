<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserTag extends Model
{
    public $table = "usertags";
    protected $fillable= ['id','user_id','post_id','comment_id','description','tag_user_id'];
}
