<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;

class FirebaseAuthService
{
    protected Auth $auth;

    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(storage_path('firebase/firebase_credentials.json'));
        $this->auth = $factory->createAuth();
    }

    public function verifyIdToken(string $idToken)
    {
        try {
            return $this->auth->verifyIdToken($idToken);
        } catch (FailedToVerifyToken $e) {
            return null;
        }
    }

    public function getUidFromToken(string $idToken): ?string
    {
        $verifiedToken = $this->verifyIdToken($idToken);
        return $verifiedToken ? $verifiedToken->claims()->get('sub') : null;
    }
}
