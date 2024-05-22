<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReportStatus extends Model
{
    public $table = "report_status";
    protected $fillable = ['id','user_id','post_id','report_id','status'];

    public function report()
    {
        return $this->belongsTo(Report::class,'report_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class,'post_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
