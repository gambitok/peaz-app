<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserInterested extends Model
{
    public $table="user_interested";
    protected $fillable = ['id','user_id','type','title'];
}
