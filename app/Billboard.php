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
        if(!empty($val)){
            return Storage::disk('s3')->url($val);
        }
        return '';
    }

    public function getLogoFileAttribute($val)
    {
        if(!empty($val)){
            return Storage::disk('s3')->url($val);
        }
        return '';
    }

    public function getHorizontalFileAttribute($val)
    {
        if(!empty($val)){
            return Storage::disk('s3')->url($val);
        }
        return '';
    }

    public function getVideoFileAttribute($val)
    {
        if(!empty($val)){
            return Storage::disk('s3')->url($val);
        }
        return '';
    }
}
