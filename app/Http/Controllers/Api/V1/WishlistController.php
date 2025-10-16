<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WishlistController extends Controller
{
    public function index()
    {
        // Use Eloquent to leverage accessors/casts and compute effective price
        $products = Product::with('user')
            ->select('products.*')
            ->join('wishlist_items', 'wishlist_items.product_id', '=', 'products.id')
            ->where('wishlist_items.user_id', auth()->id())
            ->orderBy('wishlist_items.created_at', 'desc')
            ->get();

        $items = $products->map(function ($product) {
            // Determine base price: use product price if > 0, otherwise fallback to MRP
            $basePrice = (float) $product->price > 0 ? (float) $product->price : (float) $product->mrp;
            // Apply discount if available
            if ((float) $product->discount_percentage > 0 && $basePrice > 0) {
                $effectivePrice = $basePrice * (1 - ((float) $product->discount_percentage / 100));
            } else {
                $effectivePrice = $basePrice;
            }

            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => [
                    'raw' => round($effectivePrice, 2),
                    'formatted' => '₹' . number_format($effectivePrice, 2),
                ],
                'main_image' => $product->main_image,
                'vendor' => $product->user ? [
                    'id' => $product->user->id,
                    'name' => $product->user->name,
                ] : null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $items,
        ]);
    }

    public function store(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        DB::table('wishlist_items')->updateOrInsert(
            [
                'user_id' => auth()->id(),
                'product_id' => $product->id,
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Product added to wishlist',
        ], 201);
    }

    public function destroy($productId)
    {
        DB::table('wishlist_items')
            ->where('wishlist_items.user_id', auth()->id())
            ->where('product_id', (int) $productId)
            ->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Product removed from wishlist',
        ]);
    }
}