<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorDetail extends Model
{
    protected $fillable = [
        'user_id',
        'business_type',
        'gst_number',
        'pan_number',
        'business_address',
        'city',
        'state',
        'pincode',
        'contact_person_name',
        'contact_person_phone',
        'alternate_phone',
        'bank_name',
        'account_number',
        'ifsc_code',
        'branch_name',
        'gst_certificate_path',
        'pan_card_path',
        'business_license_path',
        'bank_statement_path',
        'status',
        'rejection_reason',
        'approved_at'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}