<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PostThumbnail extends Model
{
    protected $fillable = ['post_id', 'file', 'file_type', 'thumbnail', 'type', 'title', 'description'];

    /**
     * Get the post that owns the thumbnail.
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function getFileAttribute($val)
    {
        if (!empty($val) && !preg_match('#^https?://#', $val)) {
            return Storage::disk('s3')->url($val);
        }

        return $val;
    }

    public function getThumbnailAttribute($val)
    {
        if (!empty($val) && !preg_match('#^https?://#', $val)) {
            return Storage::disk('s3')->url($val);
        }

        return $val;
    }

    public function setFileAttribute($val)
    {
        $this->attributes['file'] = $val;
    }

    public function setThumbnailAttribute($val)
    {
        $this->attributes['thumbnail'] = $val;
    }
}

