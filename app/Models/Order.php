<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'shipping_address',
        'payment_method',
        'total_amount',
        'status',
        'contact_name',
        'contact_email',
        'contact_phone'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function products() {
        return $this->belongsToMany(Product::class, 'order_product')->withPivot('quantity');
    }

    // If orders reference a company_id directly, expose relation; otherwise, products->company should be used.
    public function company() {
        return $this->belongsTo(Company::class);
    }
}

