<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dietary extends Model
{
    public $table = "dietaries";
    protected $fillable = ['id','name'];

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_dietary', 'dietary_id', 'post_id');
    }
}
