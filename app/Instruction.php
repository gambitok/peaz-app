<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Instruction extends Model
{
    public $table = "instruction";
    protected $fillable = ['id','user_id','post_id','title','file','description','thumbnail','type'];

    
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

    

    public function saveInstruction($data=[],$object_id=0,$object = null){
        if(!empty($object)){
            //
        }
        elseif($object_id > 0){
            $object = $this->find($object_id);
        }
        else{
            $object = new Instruction();
        }
        $object->fill($data);
        $object->save();

        return $object;
    }
}
