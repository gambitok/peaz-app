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

        Log::info('Private key path: ' . $privateKeyPath);
        Log::info('Public key path: ' . $publicKeyPath);

        try {
            $privateKey = new CryptKey($privateKeyPath, null, false);
            $publicKey = new CryptKey($publicKeyPath, null, false);

            Log::info('Keys loaded successfully.');
        } catch (\LogicException $e) {
            Log::error('An error occurred: ' . $e->getMessage());
        }

        return $next($request);
    }
}
