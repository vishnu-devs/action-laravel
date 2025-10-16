<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_name',
        'business_type',
        'gst_number',
        'pan_number',
        'address',
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
        'status',
        'rejection_reason',
        'approved_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documents()
    {
        return $this->hasMany(VendorDocument::class);
    }
}