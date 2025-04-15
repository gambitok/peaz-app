<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    public $table = "tags";
    protected $fillable = ['id','name'];

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_tag', 'tag_id', 'post_id');
    }

    public function filters()
    {
        return $this->belongsToMany(Filter::class);
    }
}
