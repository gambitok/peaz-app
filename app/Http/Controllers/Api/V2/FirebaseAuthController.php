<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Services\FirebaseAuthService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FirebaseAuthController extends Controller
{
    protected FirebaseAuthService $firebase;

    public function __construct(FirebaseAuthService $firebase)
    {
        $this->firebase = $firebase;
    }

    public function loginWithFirebase(Request $request)
    {
        $request->validate([
            'firebase_token' => 'required|string',
            'mobile' => 'nullable|string|required_without:email',
            'email' => 'nullable|email|required_without:mobile',
        ]);

        // Знайти користувача або за mobile, або за email
        $user = User::when($request->mobile, function ($query) use ($request) {
            return $query->where('mobile', $request->mobile);
        })
            ->when($request->email, function ($query) use ($request) {
                return $query->orWhere('email', $request->email);
            })
            ->first();

        if (!$user) {
            $randomPassword = Str::random(8);

            $user = User::create([
                'mobile' => $request->mobile,
                'email' => $request->email,
                'password' => Hash::make($randomPassword),
                'firebase_token' => $request->firebase_token,
            ]);
        } else {
            $user->update([
                'firebase_token' => $request->firebase_token,
            ]);
        }

        $tokenResult = $user->createToken('Personal Access Token');

        return response()->json([
            'message' => 'Logged in successfully',
            'user' => [
                'id' => $user->id,
                'mobile' => $user->mobile,
                'email' => $user->email,
                'name' => $user->name,
            ],
            'token' => $tokenResult->accessToken,
        ]);
    }

}
