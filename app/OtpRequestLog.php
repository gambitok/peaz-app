<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class OtpRequestLog extends Model
{
    protected $guarded = [];

    private $request;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->request = request();
    }

    public function scopeType($query)
    {
        $query->where("type", $this->request->type);
    }

    public function scopeInDay($query)
    {
        $query->whereDate('created_at', Carbon::now()->toDate());
    }
}
