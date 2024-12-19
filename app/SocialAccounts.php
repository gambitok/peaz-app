<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialAccounts extends Model
{
    public $table = 'social_accounts';
    protected $fillable = ['id','user_id','provider_id','provider'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
