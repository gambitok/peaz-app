<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    public $table = "ingredients";
    protected $fillable = ['id','name','type','weight'];

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_ingredient')
            ->withPivot('measurement')
            ->withTimestamps();
    }

    public function saveIngredient($data = [], $object_id = 0, $object = null)
    {
        if(!empty($object)){
            //
        }
        elseif($object_id > 0){
            $object = $this->find($object_id);
        }
        else{
            $object = new Ingredient();
        }
        $object->fill($data);
        $object->save();

        return $object;
    }

    public function getNameAttribute($val)
    {
        if(!empty($val)){
            return $val;
        }
        return '';
    }

    public function getTypeAttribute($val)
    {
        if(!empty($val)){
            return $val;
        }
        return '';
    }

    public function getMeasurementAttribute($val)
    {
        if(!empty($val)){
            return $val;
        }
        return '';
    }

}
