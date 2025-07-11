<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Restaurant extends Model
{

    protected $fillable = [
        'file',
        'title',
        'link',
        'user_id',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFileAttribute($val)
    {
        if (!empty($val) && !preg_match('#^https?://#', $val)) {
            return Storage::disk('s3')->url($val);
        }

        return $val;
    }
}
