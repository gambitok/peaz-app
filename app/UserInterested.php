<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserInterested extends Model
{
    public $table = 'user_interested';
    protected $fillable = ['id','user_id','type','title'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
