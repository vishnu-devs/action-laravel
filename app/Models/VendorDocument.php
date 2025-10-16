<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorDocument extends Model
{
    protected $fillable = [
        'vendor_request_id',
        'document_type',
        'path',
    ];

    public function vendorRequest()
    {
        return $this->belongsTo(VendorRequest::class);
    }
}