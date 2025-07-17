<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostIngredient extends Model
{
    public $table = "post_ingredient";
    protected $fillable = ['id','user_id','post_id','name','type','measurement', 'ingredient_id'];

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
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
            $object = new PostIngredient();
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
