<?php

namespace App\Services;

use Illuminate\Http\Request;
use Jenssegers\Agent\Facades\Agent;

class DeviceDetectionService
{
    public function __construct()
    {
    }

    public function getDeviceInfo(Request $request): array
    {
        return [
            'device_name' => $this->getDeviceName(),
            'device_type' => $this->getDeviceType(),
            'browser' => $this->getBrowser(),
            'ip_address' => $request->ip(),
            'location' => $this->getLocation($request->ip())
        ];
    }

    protected function getDeviceName(): string
    {
        if (Agent::isDesktop()) {
            return 'Desktop';
        }
        
        return Agent::device() ?? 'Unknown Device';
    }

    protected function getDeviceType(): string
    {
        if (Agent::isDesktop()) {
            return 'desktop';
        } elseif (Agent::isTablet()) {
            return 'tablet';
        } elseif (Agent::isMobile()) {
            return 'mobile';
        }
        
        return 'unknown';
    }

    protected function getBrowser(): string
    {
        return Agent::browser() . ' ' . Agent::version(Agent::browser());
    }

    protected function getLocation(string $ip): string
    {
        // For demo purposes, we'll just return the IP
        // In production, you might want to use a geolocation service
        return $ip;
    }
}