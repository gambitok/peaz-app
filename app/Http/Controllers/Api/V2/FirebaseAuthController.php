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
            'uid' => 'required|string',
            'email' => 'nullable|email|required_without:mobile',
            'mobile' => 'nullable|string|required_without:email',
            'name' => 'nullable|string',
        ]);

        $user = User::where('firebase_uid', $request->uid)->first();

        if (!$user) {
            $randomPassword = Str::random(12);

            $user = User::create([
                'firebase_uid' => $request->uid,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'name' => $request->name,
                'password' => Hash::make($randomPassword),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'User created successfully.',
                'user' => $user,
                'token' => $token,
                'generated_password' => $randomPassword,
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'user' => $user,
            'token' => $token,
        ]);
    }
}
