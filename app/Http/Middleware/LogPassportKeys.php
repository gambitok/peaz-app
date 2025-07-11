<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use League\OAuth2\Server\CryptKey;

class LogPassportKeys
{
    public function handle($request, Closure $next)
    {
        $privateKeyPath = config('passport.private_key') ?? env('PASSPORT_PRIVATE_KEY');
        $publicKeyPath = config('passport.public_key') ?? env('PASSPORT_PUBLIC_KEY');

        Log::info("Private Key Path: " . $privateKeyPath);
        Log::info("Public Key Path: " . $publicKeyPath);

        if (!file_exists($privateKeyPath)) {
            Log::error("Private key file does not exist at: " . $privateKeyPath);
            return response()->json(['error' => 'Private key not found'], 500);
        }

        if (!file_exists($publicKeyPath)) {
            Log::error("Public key file does not exist at: " . $publicKeyPath);
            return response()->json(['error' => 'Public key not found'], 500);
        }

        try {
            $privateKey = new CryptKey($privateKeyPath, null);
            $publicKey = new CryptKey($publicKeyPath, null);
        } catch (\Exception $e) {
            Log::error("Error initializing CryptKey: " . $e->getMessage());
            return response()->json(['error' => 'Error initializing keys'], 500);
        }

        Log::info("Successfully initialized keys");

        return $next($request);
    }
}
