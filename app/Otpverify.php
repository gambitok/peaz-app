<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Otpverify extends Model
{
    public $table = "otp_verify";
    protected $fillable = ['id','email','otp'];

}
