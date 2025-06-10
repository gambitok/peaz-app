<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Billboard extends Model
{

    protected $fillable = [
        'file',
        'logo_file',
        'horizontal_file',
        'video_file',
        'link',
        'user_id',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }

    public function getFileAttribute($val)
    {
        if (!empty($val) && !preg_match('#^https?://#', $val)) {
            return Storage::disk('s3')->url($val);
        }

        return $val;
    }

    public function getLogoFileAttribute($val)
    {
        if (!empty($val) && !preg_match('#^https?://#', $val)) {
            return Storage::disk('s3')->url($val);
        }

        return $val;
    }

    public function getHorizontalFileAttribute($val)
    {
        if (!empty($val) && !preg_match('#^https?://#', $val)) {
            return Storage::disk('s3')->url($val);
        }

        return $val;
    }

    public function getVideoFileAttribute($val)
    {
        if (!empty($val) && !preg_match('#^https?://#', $val)) {
            return Storage::disk('s3')->url($val);
        }

        return $val;
    }
}
