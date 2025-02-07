<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
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
}
