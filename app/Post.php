<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    public $table = "posts";
    protected $fillable = ['id','title','user_id','type','file','thumbnail','caption','serving_size','hours','minutes','dietary','tags','cuisines','not_interested'];

    public function getFileAttribute($val)
    {
        if(!empty($val)){
            return Storage::disk('s3')->url($val);
        }
        return get_asset($val, false, get_constants('default.user_image'));
    }

    public function getThumbnailAttribute($val)
    {
        if(!empty($val)){
            return Storage::disk('s3')->url($val);
        }
        return get_asset($val, false, get_constants('default.user_image'));
    }
    
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function ingredient()
    {
        return $this->hasMany(Ingredient::class,'post_id','id');
    }

    public function instruction()
    {
        return $this->hasMany(Instruction::class,'post_id','id');
    }

    public function comment()
    {
        return $this->hasMany(Comment::class,'post_id','id');
    }

   
    public function reply()
    {
        return $this->hasMany(Comment::class,'comment_id','id');
    }

    public function postlike()
    {
        return $this->hasMany(Postlike::class,'post_id','id');
    }
    public function getIsRatingAttribute($val){
       
        if($val == 'true'){
            return true;
        }
        return false;
    }
    // public function total_postlike()
    // {
    //     return $this->hasMany(Postlike::class,'post_id','id');
    // }
    public function getAvgRatingAttribute($val)
    {
        if(!empty($val) || $val > 0){
            return number_format((float)$val, 1, '.', '');
        }
        else{
            return "0";
        }

    }
    public function scopeAvgRating($query){
        return $query->addSelect(DB::raw('(SELECT AVG(rating)
 FROM   comments WHERE posts.id=post_id AND type=1) as avg_rating')); 
    }
    public function scopeIsRating($query,$user_id){
        
        return $query->selectRaw("CASE WHEN EXISTS (SELECT * FROM comments WHERE posts.id = post_id AND type = 1 AND user_id = ?) THEN 'true' ELSE 'false' END as is_rating",[$user_id]);

    }

    public function savePost($data=[],$object_id=0,$object = null){
        if(!empty($object)){
            //
        }
        elseif($object_id > 0){
            $object = $this->find($object_id);
        }
        else{
            $object = new Post();
        }
        $object->fill($data);
        $object->save();

        return $object;
    }

    
}
