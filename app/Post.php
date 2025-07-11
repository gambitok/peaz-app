<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    public $table = "posts";
    protected $fillable = ['id','title','user_id','type','file','thumbnail','caption','serving_size','hours','minutes','not_interested','status','created_at','verified'];

    protected $attributes = [
        'status' => 1,
    ];

    public function getTagsAttribute($value)
    {
        if ($this->relationLoaded('tags')) {
            return $this->getRelation('tags');
        }
        return $value;
    }

    public function getCuisinesAttribute($value)
    {
        if ($this->relationLoaded('cuisines')) {
            return $this->getRelation('cuisines');
        }
        return $value;
    }

    public function getDietariesAttribute($value)
    {
        if ($this->relationLoaded('dietaries')) {
            return $this->getRelation('dietaries');
        }
        return $value;
    }

    public function thumbnails()
    {
        return $this->hasMany(PostThumbnail::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tag', 'post_id', 'tag_id');
    }

    public function dietaries()
    {
        return $this->belongsToMany(Dietary::class, 'post_dietary', 'post_id', 'dietary_id');
    }

    public function cuisines()
    {
        return $this->belongsToMany(Cuisine::class, 'post_cuisine', 'post_id', 'cuisine_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'post_id');
    }

    public function report_statuses()
    {
        return $this->hasMany(ReportStatus::class);
    }

    public function postLikes()
    {
        return $this->hasMany(PostLike::class, 'post_id');
    }

    public function instructions()
    {
        return $this->hasMany(Instruction::class, 'post_id');
    }

    public function postIngredients()
    {
        return $this->hasMany(PostIngredient::class, 'post_id');
    }
    public function ingredients()
    {
        return $this->hasMany(PostIngredient::class, 'post_id');
    }
//
//    public function ingredient()
//    {
//        return $this->hasMany(Ingredient::class,'post_id','id');
//    }

    public function getFileAttribute($val)
    {
        if (!empty($val) && !preg_match('#^https?://#', $val)) {
            return Storage::disk('s3')->url($val);
        }

        return $val;
    }

    public function getThumbnailAttribute($val)
    {
        if (!empty($val) && !preg_match('#^https?://#', $val)) {
            return Storage::disk('s3')->url($val);
        }

        return $val;
    }

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
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
        return $this->hasMany(PostLike::class,'post_id','id');
    }

    public function getIsRatingAttribute($val)
    {
        if($val == 'true'){
            return true;
        }
        return false;
    }

    public function getAvgRatingAttribute($val)
    {
        return number_format($val ?? 0, 1);
    }

    public function scopeAvgRating($query)
    {
        return $query->addSelect(DB::raw('(SELECT AVG(rating) FROM comments WHERE posts.id = post_id AND rating IS NOT NULL AND rating > 0) as avg_rating'));
    }

    public function scopeIsRating($query,$user_id)
    {
        return $query->selectRaw("CASE WHEN EXISTS (SELECT * FROM comments WHERE posts.id = post_id AND AND user_id = ? AND rating IS NOT NULL AND rating > 0) THEN 'true' ELSE 'false' END as is_rating",[$user_id]);
    }

    public function savePost($data=[],$object_id=0,$object = null)
    {
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
