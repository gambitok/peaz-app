<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserInterest extends Model
{

    protected $fillable = [
        'user_id',
        'tags',
        'dietaries',
        'cuisines',
    ];

    protected $casts = [
        'tags' => 'array',
        'dietaries' => 'array',
        'cuisines' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function dietaries()
    {
        return $this->belongsToMany(Dietary::class);
    }

    public function cuisines()
    {
        return $this->belongsToMany(Cuisine::class);
    }
}
