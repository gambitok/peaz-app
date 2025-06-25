<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Filter extends Model
{
    public $table = "filters";
    protected $fillable = ['name'];

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'filter_tag');
    }

    public function dietaries()
    {
        return $this->belongsToMany(Dietary::class, 'filter_dietary');
    }

    public function cuisines()
    {
        return $this->belongsToMany(Cuisine::class, 'filter_cuisine');
    }
}
