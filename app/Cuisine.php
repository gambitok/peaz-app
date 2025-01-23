<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cuisine extends Model
{
    public $table = "cuisines";
    protected $fillable = ['id','name'];

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_cuisine', 'cuisine_id', 'post_id');
    }
}
