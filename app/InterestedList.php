<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class InterestedList extends Model
{
    use SoftDeletes;
    public $table = "interested_list";
    protected $fillable= ['id','category_id','image','type','title'];


    public function getImageAttribute($val)
    {
        if(!empty($val)){
            return Storage::disk('s3')->url($val);
        }
        return get_asset($val, false, get_constants('default.interest_image'));
    }

    public function category()
    {
        return $this->belongsTo(Category::class,'category_id','id');
    }
    
}
