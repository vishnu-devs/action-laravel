<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'product_id',
        'rating',
        'comment',
        'pros',
        'cons',
        'images',
        'is_verified_purchase',
        'helpful_votes'
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'pros' => 'array',
        'cons' => 'array',
        'images' => 'array',
        'is_verified_purchase' => 'boolean',
        'helpful_votes' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected static function boot()
    {
        parent::boot();

        // Update product rating stats when a review is created/updated/deleted
        static::saved(function ($review) {
            $review->product->updateRatingStats();
        });

        static::deleted(function ($review) {
            $review->product->updateRatingStats();
        });
    }
}