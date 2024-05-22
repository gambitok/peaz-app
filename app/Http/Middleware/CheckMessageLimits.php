<?php

namespace App\Http\Middleware;

use App\OtpRequestLog;
use App\OtpRequestLogs;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CheckMessageLimits
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $total = OtpRequestLog::where(function ($q) use ($request) {
            $q->where("ip", $request->ip())->where("country_code", $request->country_code)->where("mobile", $request->mobile);
        })->InDay()->count();

        if ($total >= 3) {
            send_response(412, __("api.daily_limit_reached"));
        }

        return $next($request);
    }
}
