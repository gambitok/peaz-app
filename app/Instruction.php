<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Post;

class Instruction extends Model
{
    public $table = "instruction";
    protected $fillable = ['id','user_id','post_id','title','file','description','thumbnail','type'];

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

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
