<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Billboard extends Model
{

    protected $fillable = [
        'title',
        'caption',
        'file',
        'link',
        'tag_id',
        'verified',
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
}
