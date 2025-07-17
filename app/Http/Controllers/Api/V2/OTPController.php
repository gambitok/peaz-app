<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;

class OTPController extends Controller
{

    public function sendOtpMobile(Request $request)
    {
        $request->validate(['mobile' => 'required|string']);

        $otp = rand(100000, 999999);
        $mobile = $request->input('mobile');

        Cache::put("otp_mobile_{$mobile}", $otp, now()->addMinutes(5));

        return response()->json(['message' => 'OTP sent to mobile', 'otp' => $otp]);
    }

    public function verifyOtpMobile(Request $request)
    {
        $request->validate([
            'mobile' => 'required|string',
            'otp' => 'required|numeric',
        ]);

        $cachedOtp = Cache::get("otp_mobile_{$request->mobile}");

        if ($cachedOtp && $cachedOtp == $request->otp) {
            Cache::forget("otp_mobile_{$request->mobile}");

            $user = User::where('mobile', $request->mobile)->first();
            if ($user) {
                $user->verified = 1;
                $user->save();
            }

            return response()->json(['message' => 'Mobile OTP verified successfully']);
        }

        return response()->json(['error' => 'Invalid or expired OTP'], 400);
    }

    public function sendOtpEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $otp = rand(100000, 999999);
        $email = $request->input('email');

        Cache::put("otp_email_{$email}", $otp, now()->addMinutes(5));

        Mail::raw("Your OTP is $otp", function ($message) use ($email) {
            $message->to($email)->subject('Your OTP Code');
        });

        return response()->json(['message' => 'OTP sent to email', 'otp' => $otp]);
    }

    public function verifyOtpEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|numeric',
        ]);

        $cachedOtp = Cache::get("otp_email_{$request->email}");

        if ($cachedOtp && $cachedOtp == $request->otp) {
            Cache::forget("otp_email_{$request->email}");

            $user = \App\User::where('email', $request->email)->first();
            if ($user) {
                $user->verified = 1;
                $user->save();
            }

            return response()->json(['message' => 'Email OTP verified successfully']);
        }

        return response()->json(['error' => 'Invalid or expired OTP'], 400);
    }

}
