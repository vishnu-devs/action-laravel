<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class VerifyDeviceFingerprint
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $token = $request->user()->currentAccessToken();
        $deviceFingerprint = $request->header('X-Device-Fingerprint');
        
        if (!$deviceFingerprint) {
            return response()->json(['message' => 'Device fingerprint is required.'], 401);
        }

        // Check if the device fingerprint matches
        if ($token->device_fingerprint !== $deviceFingerprint) {
            // If fingerprint doesn't match, revoke the token
            $token->delete();
            return response()->json(['message' => 'Invalid device. Please login again.'], 401);
        }

        return $next($request);
    }
}