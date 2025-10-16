<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    protected $fillable = [
        'name',
        'token',
        'abilities',
        'device_name',
        'device_type',
        'browser',
        'ip_address',
        'location',
        'device_fingerprint'
    ];
}