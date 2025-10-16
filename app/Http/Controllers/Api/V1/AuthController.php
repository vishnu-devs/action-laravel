<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DeviceDetectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    protected $deviceDetectionService;

    public function __construct(DeviceDetectionService $deviceDetectionService)
    {
        $this->deviceDetectionService = $deviceDetectionService;
    }

    /**
     * Determine the primary role to expose to the frontend with clear precedence.
     */
    private function primaryRole(User $user): string
    {
        // Determine role using assigned role names, agnostic of guard
        try {
            $roles = $user->getRoleNames()->map(fn ($r) => strtolower($r));
        } catch (\Throwable $e) {
            // Fallback to hasRole checks if getRoleNames fails
            $roles = collect([
                $user->hasRole('super_admin') ? 'super_admin' : null,
                $user->hasRole('admin') ? 'admin' : null,
                $user->hasRole('vendor') ? 'vendor' : null,
            ])->filter();
        }

        if ($roles->contains('super_admin')) {
            return 'super_admin';
        }
        if ($roles->contains('admin')) {
            return 'admin';
        }
        if ($roles->contains('vendor')) {
            return 'vendor';
        }
        return 'customer';
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        // Create token and persist device details
        $deviceInfo = $this->deviceDetectionService->getDeviceInfo($request);
        $newToken   = $user->createToken('auth_token', []);
        $tokenModel = $newToken->accessToken; // PersonalAccessToken model
        $tokenModel->forceFill([
            'device_name'        => $deviceInfo['device_name'],
            'device_type'        => $deviceInfo['device_type'],
            'browser'            => $deviceInfo['browser'],
            'ip_address'         => $deviceInfo['ip_address'],
            'location'           => $deviceInfo['location'],
            'device_fingerprint' => $request->header('X-Device-Fingerprint'),
        ])->save();
        $token = $newToken->plainTextToken;
        
        // Assign customer role
        $user->assignRole('customer');
        // Expose primary role in response for frontend
        $user->role = $this->primaryRole($user);

        // Backend log: record role and token id for Google login
        try {
            $tokenId = explode('|', $token)[0] ?? 'unknown';
            $logLine = sprintf('[%s] google_login: user_id=%d role=%s token_id=%s device_fingerprint=%s', now()->toDateTimeString(), $user->id, $user->role, $tokenId, $deviceFingerprint);
            File::append(storage_path('logs/login_role.log'), $logLine . PHP_EOL);
            Log::info('Google login issued token', [ 'user_id' => $user->id, 'role' => $user->role, 'token_id' => $tokenId ]);
        } catch (\Throwable $e) {
            // swallow logging errors
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ], 201);
    }

    public function googleLogin(Request $request)
    {
        // Minimal Google login: trust provided email/name and create/login user
        $request->validate([
            'email' => 'required|string|email',
            'name' => 'sometimes|string|max:255',
            'id_token' => 'sometimes|string'
        ]);

        $deviceFingerprint = $request->header('X-Device-Fingerprint');
        if (!$deviceFingerprint) {
            return response()->json([
                'status' => 'error',
                'message' => 'Device fingerprint is required'
            ], 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            $user = User::create([
                'name' => $request->name ?? explode('@', $request->email)[0],
                'email' => $request->email,
                // Set random password; not used for Google login
                'password' => \Illuminate\Support\Str::random(32),
            ]);
            // Assign customer role
            $user->assignRole('customer');
        }

        // Capture device info
        $deviceInfo = $this->deviceDetectionService->getDeviceInfo($request);

        // Access token with device details
        $newToken   = $user->createToken('auth_token', []);
        $tokenModel = $newToken->accessToken;
        $tokenModel->forceFill([
            'device_name'        => $deviceInfo['device_name'],
            'device_type'        => $deviceInfo['device_type'],
            'browser'            => $deviceInfo['browser'],
            'ip_address'         => $deviceInfo['ip_address'],
            'location'           => $deviceInfo['location'],
            'device_fingerprint' => $deviceFingerprint,
        ])->save();
        $token = $newToken->plainTextToken;

        // Refresh token
        $newRefresh   = $user->createToken('refresh_token', ['refresh-token']);
        $refreshModel = $newRefresh->accessToken;
        $refreshModel->forceFill([
            'device_name'        => $deviceInfo['device_name'],
            'device_type'        => $deviceInfo['device_type'],
            'browser'            => $deviceInfo['browser'],
            'ip_address'         => $deviceInfo['ip_address'],
            'location'           => $deviceInfo['location'],
            'device_fingerprint' => $deviceFingerprint,
        ])->save();
        $refreshToken = $newRefresh->plainTextToken;

        // Set refresh token as HttpOnly cookie
        $cookie = cookie('refresh_token', $refreshToken, 60 * 24 * 30); // 30 days

        // Expose primary role in response for frontend
        $user->role = $this->primaryRole($user);

        // Backend log: record role and token id for email/password login
        try {
            $tokenId = explode('|', $token)[0] ?? 'unknown';
            $logLine = sprintf('[%s] login: user_id=%d role=%s token_id=%s device_fingerprint=%s', now()->toDateTimeString(), $user->id, $user->role, $tokenId, $deviceFingerprint);
            File::append(storage_path('logs/login_role.log'), $logLine . PHP_EOL);
            Log::info('Login issued token', [ 'user_id' => $user->id, 'role' => $user->role, 'token_id' => $tokenId ]);
        } catch (\Throwable $e) {
            // swallow logging errors
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Logged in with Google successfully',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ])->withCookie($cookie);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::make($request, [
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Get device fingerprint from headers
        $deviceFingerprint = $request->header('X-Device-Fingerprint');
        $deviceType = $request->header('X-Device-Type');
        $browser = $request->header('X-Browser');
        $platform = $request->header('X-Platform');

        if (!$deviceFingerprint) {
            return response()->json([
                'status' => 'error',
                'message' => 'Device fingerprint is required'
            ], 400);
        }

        // Capture device info
        $deviceInfo = $this->deviceDetectionService->getDeviceInfo($request);

        // Access token with device details
        $newToken   = $user->createToken('auth_token', []);
        $tokenModel = $newToken->accessToken;
        $tokenModel->forceFill([
            'device_name'        => $deviceInfo['device_name'],
            'device_type'        => $deviceInfo['device_type'],
            'browser'            => $deviceInfo['browser'],
            'ip_address'         => $deviceInfo['ip_address'],
            'location'           => $deviceInfo['location'],
            'device_fingerprint' => $deviceFingerprint,
        ])->save();
        $token = $newToken->plainTextToken;

        // Refresh token with same device details
        $newRefresh   = $user->createToken('refresh_token', ['refresh-token']);
        $refreshModel = $newRefresh->accessToken;
        $refreshModel->forceFill([
            'device_name'        => $deviceInfo['device_name'],
            'device_type'        => $deviceInfo['device_type'],
            'browser'            => $deviceInfo['browser'],
            'ip_address'         => $deviceInfo['ip_address'],
            'location'           => $deviceInfo['location'],
            'device_fingerprint' => $deviceFingerprint,
        ])->save();
        $refreshToken = $newRefresh->plainTextToken;

        // Set refresh token as HttpOnly cookie
        $cookie = cookie('refresh_token', $refreshToken, 60 * 24 * 30); // 30 days

        // Expose primary role in response for frontend
        $user->role = $this->primaryRole($user);

        return response()->json([
            'status' => 'success',
            'message' => 'User logged in successfully',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ])->withCookie($cookie);
    }

    public function logout(Request $request)
    {
        // Delete all tokens
        $request->user()->tokens()->delete();

        $response = response()->json([
            'status' => 'success',
            'message' => 'User logged out successfully'
        ]);

        // Remove refresh token cookie
        return $response->cookie(
            'refresh_token',
            '',
            -1,
            '/'
        );
    }

    public function refreshToken(Request $request)
    {
        try {
            $refreshToken = $request->cookie('refresh_token');
            
            if (!$refreshToken) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Refresh token not found'
                ], 401);
            }

            // Get token ID and user ID from refresh token
            $tokenId = explode('|', $refreshToken)[0];
            $token = PersonalAccessToken::findOrFail($tokenId);
            $user = $token->tokenable;

            if (!$token || !$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid refresh token'
                ], 401);
            }

            // Check if token has refresh-token ability
            if (!$token->can('refresh-token')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid token type'
                ], 401);
            }

            // Get device fingerprint from headers
            $deviceFingerprint = $request->header('X-Device-Fingerprint');
            
            // Verify device fingerprint matches the one stored with the refresh token
            if ($token->device_fingerprint !== $deviceFingerprint) {
                $token->delete();
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid device. Please login again.'
                ], 401);
            }

            // Create new tokens
            $newToken = $user->createToken('auth_token', [])->plainTextToken;
            $newRefreshToken = $user->createToken('refresh_token', ['refresh-token'])->plainTextToken;

            // Delete old refresh token
            $token->delete();

            // Set new refresh token as HttpOnly cookie
            $cookie = cookie('refresh_token', $newRefreshToken, 60 * 24 * 30); // 30 days

            // Expose current primary role to frontend to prevent stale role
            $user->role = $this->primaryRole($user);
            return response()->json([
                'status' => 'success',
                'message' => 'Token refreshed successfully',
                'data' => [
                    'token' => $newToken,
                    'user' => $user
                ]
            ])->withCookie($cookie);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error refreshing token'
            ], 401);
        }
    }

    public function user(Request $request)
    {
        $user = $request->user();
        // Expose primary role for frontend compatibility
        if ($user) {
            $user->role = $this->primaryRole($user);
        }
        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $user
            ]
        ]);
    }
}