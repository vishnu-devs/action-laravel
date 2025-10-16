<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\DeviceDetectionService;
use Symfony\Component\HttpFoundation\Response;

class VerifyDeviceAndIp
{
    protected $deviceDetectionService;

    public function __construct(DeviceDetectionService $deviceDetectionService)
    {
        $this->deviceDetectionService = $deviceDetectionService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->user()->currentAccessToken();
        
        if (!$token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $currentDeviceInfo = $this->deviceDetectionService->getDeviceInfo($request);

        // Primary Check: Device Verification
        $deviceMismatch = 
            $token->device_name !== $currentDeviceInfo['device_name'] ||
            $token->device_type !== $currentDeviceInfo['device_type'] ||
            $token->browser !== $currentDeviceInfo['browser'];

        if ($deviceMismatch) {
            $token->delete();
            return response()->json([
                'message' => 'Security verification failed. Please log in again.',
                'reason' => 'Device mismatch detected'
            ], 401);
        }

        // Secondary Check: IP Verification (Soft Check)
        $ipMismatch = $token->ip_address !== $request->ip();
        if ($ipMismatch) {
            // Just log the IP change but don't revoke the token
            // You might want to add logging here for security monitoring
            $token->ip_address = $request->ip();
            $token->save();
        }

        return $next($request);
    }
}