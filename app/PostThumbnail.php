<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostThumbnail extends Model
{
    protected $fillable = ['post_id', 'thumbnail', 'type'];

    /**
     * Get the post that owns the thumbnail.
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
