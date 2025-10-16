<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'category',
        'brand',
        'model',
        'price',
        'stock_quantity',
        'mrp',
        'discount_percentage',
        'specifications',
        'highlights',
        'main_image',
        'additional_images',
        'is_featured',
        'is_active',
        'weight',
        'length',
        'width',
        'height',
        'free_shipping',
        'user_id'
    ];

    protected $casts = [
        'specifications' => 'array',
        'highlights' => 'array',
        'additional_images' => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'free_shipping' => 'boolean',
        'price' => 'decimal:2',
        'mrp' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'average_rating' => 'decimal:2',
        'weight' => 'decimal:2',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });

        static::updating(function ($product) {
            if ($product->isDirty('name') && empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function company() {
        return $this->belongsTo(Company::class);
    }

    public function reviews() {
        return $this->hasMany(Review::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_product')->withPivot('quantity');
    }

    public function updateRatingStats()
    {
        $stats = $this->reviews()
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as total_reviews')
            ->first();

        $this->update([
            'average_rating' => $stats->avg_rating ?? 0,
            'review_count' => $stats->total_reviews ?? 0
        ]);
    }

    public function getDiscountedPriceAttribute()
    {
        if ($this->discount_percentage > 0) {
            return $this->price * (1 - $this->discount_percentage / 100);
        }
        return $this->price;
    }

    public function getSavingsAttribute()
    {
        return $this->mrp - $this->discounted_price;
    }

    public function getSavingsPercentageAttribute()
    {
        if ($this->mrp > 0) {
            return round(($this->savings / $this->mrp) * 100, 2);
        }
        return 0;
    }
}
