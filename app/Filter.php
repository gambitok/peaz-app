<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Filter extends Model
{
    protected $fillable = ['name'];

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}
