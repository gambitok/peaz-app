<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Billboard extends Model
{

    protected $fillable = [
        'file',
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
}
