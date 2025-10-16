<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['user', 'reviews']);
        
        // If user is vendor, only show their products
        if (auth()->user()->hasRole('vendor')) {
            $query->where('user_id', auth()->id());
        }

        // Apply filters
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('brand')) {
            $query->where('brand', $request->brand);
        }

        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortField = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $allowedSortFields = ['price', 'created_at', 'average_rating', 'review_count'];
        
        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortOrder);
        }
        
        $products = $query->paginate($request->get('per_page', 10));

        // Transform each product of the paginator collection
        $transformed = $products->getCollection()->transform(function ($product) {
                // Calculate sold quantity from non-cancelled/non-refund-cancelled orders
                $sold = $product->orders()
                    ->whereNotIn('orders.status', ['cancelled', 'refund_cancelled'])
                    ->sum('order_product.quantity');
                $stockLeft = max(0, (int)$product->stock_quantity - (int)$sold);
                return [
                    'user_id' => $product->user_id,
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'description' => $product->description,
                    'category' => $product->category,
                    'brand' => $product->brand,
                    'model' => $product->model,
                    'price' => [
                        'raw' => $product->price,
                        'formatted' => '₹' . number_format($product->price, 2),
                        'mrp' => [
                            'raw' => $product->mrp,
                            'formatted' => '₹' . number_format($product->mrp, 2)
                        ],
                        'discount_percentage' => $product->discount_percentage,
                        'savings' => [
                            'raw' => $product->savings,
                            'formatted' => '₹' . number_format($product->savings, 2),
                            'percentage' => $product->savings_percentage
                        ]
                    ],
                    'stock_quantity' => $product->stock_quantity,
                    'sold_quantity' => (int)$sold,
                    'stock_left' => (int)$stockLeft,
                    'specifications' => $product->specifications,
                    'highlights' => $product->highlights,
                    'images' => [
                        'main' => $product->main_image ? asset('storage/' . $product->main_image) : null,
                        'additional' => collect($product->additional_images)->map(function($image) {
                            return asset('storage/' . $image);
                        })
                    ],
                    'is_featured' => $product->is_featured,
                    'is_active' => $product->is_active,
                    'ratings' => [
                        'average' => $product->average_rating,
                        'total' => $product->review_count
                    ],
                    'shipping' => [
                        'dimensions' => [
                            'weight' => $product->weight,
                            'length' => $product->length,
                            'width' => $product->width,
                            'height' => $product->height
                        ],
                        'free_shipping' => $product->free_shipping
                    ],
                    'vendor' => $product->user ? [
                        'id' => $product->user->id,
                        'name' => $product->user->name,
                        'email' => $product->user->email
                    ] : null,
                    'created_at' => [
                        'raw' => $product->created_at,
                        'formatted' => $product->created_at->format('d M Y, h:i A')
                    ]
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $transformed,
            'pagination' => [
                'total' => $products->total(),
                'per_page' => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage()
            ]
        ]);
    }

    public function store(Request $request)
    {
        // Allow only vendor, admin, or super_admin to create products
        if (!auth()->user()->hasRole('vendor') && !auth()->user()->hasRole('admin') && !auth()->user()->hasRole('super_admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to create products'
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|max:100',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'price' => 'required|numeric|min:0',
            'mrp' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'specifications' => 'nullable|array',
            'highlights' => 'nullable|array',
            'main_image' => 'required|image|max:2048', // 2MB Max
            'additional_images.*' => 'nullable|image|max:2048',
            'weight' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'free_shipping' => 'boolean'
        ]);

        // Handle main image upload
        $mainImagePath = $request->file('main_image')->store('products', 'public');

        // Handle additional images
        $additionalImagePaths = [];
        if ($request->hasFile('additional_images')) {
            foreach ($request->file('additional_images') as $image) {
                $additionalImagePaths[] = $image->store('products', 'public');
            }
        }

        $product = Product::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'category' => $request->category,
            'brand' => $request->brand,
            'model' => $request->model,
            'price' => $request->price,
            'mrp' => $request->mrp,
            'stock_quantity' => $request->stock_quantity,
            'specifications' => $request->specifications,
            'highlights' => $request->highlights,
            'main_image' => $mainImagePath,
            'additional_images' => $additionalImagePaths,
            'weight' => $request->weight,
            'length' => $request->length,
            'width' => $request->width,
            'height' => $request->height,
            'free_shipping' => $request->boolean('free_shipping', false),
            'user_id' => auth()->id()
        ]);

        // Transform response to be consistent with show/index
        $transformed = [
            'user_id' => $product->user_id,
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'category' => $product->category,
            'brand' => $product->brand,
            'model' => $product->model,
            'price' => [
                'raw' => $product->price,
                'formatted' => '₹' . number_format($product->price, 2),
                'mrp' => [
                    'raw' => $product->mrp,
                    'formatted' => '₹' . number_format($product->mrp, 2)
                ],
                'discount_percentage' => $product->discount_percentage,
                'savings' => [
                    'raw' => $product->savings ?? null,
                    'formatted' => isset($product->savings) ? ('₹' . number_format($product->savings, 2)) : null,
                    'percentage' => $product->savings_percentage ?? null
                ]
            ],
            'stock_quantity' => $product->stock_quantity,
            'sold_quantity' => 0,
            'stock_left' => (int)$product->stock_quantity,
            'specifications' => $product->specifications,
            'highlights' => $product->highlights,
            'images' => [
                'main' => $product->main_image ? asset('storage/' . $product->main_image) : null,
                'additional' => collect($product->additional_images)->map(function($image) {
                    return asset('storage/' . $image);
                })
            ],
            'is_featured' => $product->is_featured,
            'is_active' => $product->is_active,
            'ratings' => [
                'average' => $product->average_rating,
                'total' => $product->review_count
            ],
            'shipping' => [
                'dimensions' => [
                    'weight' => $product->weight,
                    'length' => $product->length,
                    'width' => $product->width,
                    'height' => $product->height
                ],
                'free_shipping' => $product->free_shipping
            ],
            'vendor' => $product->user ? [
                'id' => $product->user->id,
                'name' => $product->user->name,
                'email' => $product->user->email
            ] : null,
            'created_at' => [
                'raw' => $product->created_at,
                'formatted' => $product->created_at->format('d M Y, h:i A')
            ]
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Product created successfully',
            'data' => $transformed
        ], 201);
    }

    public function update(Request $request, Product $product)
    {
        // Check if user can edit this product
        if (auth()->user()->hasRole('vendor') && $product->user_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to edit this product'
            ], 403);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'category' => 'sometimes|required|string|max:100',
            'brand' => 'nullable|string|max:100',
            'model' => 'nullable|string|max:100',
            'price' => 'sometimes|required|numeric|min:0',
            'mrp' => 'sometimes|required|numeric|min:0',
            'stock_quantity' => 'sometimes|required|integer|min:0',
            'specifications' => 'nullable|array',
            'highlights' => 'nullable|array',
            'main_image' => 'nullable|image|max:2048',
            'additional_images.*' => 'nullable|image|max:2048',
            'weight' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'free_shipping' => 'boolean'
        ]);

        $data = $request->except(['main_image', 'additional_images']);

        // Handle main image update
        if ($request->hasFile('main_image')) {
            // Delete old image
            if ($product->main_image) {
                Storage::disk('public')->delete($product->main_image);
            }
            $data['main_image'] = $request->file('main_image')->store('products', 'public');
        }

        // Handle additional images update
        if ($request->hasFile('additional_images')) {
            // Delete old images
            foreach ($product->additional_images as $image) {
                Storage::disk('public')->delete($image);
            }
            
            $additionalImagePaths = [];
            foreach ($request->file('additional_images') as $image) {
                $additionalImagePaths[] = $image->store('products', 'public');
            }
            $data['additional_images'] = $additionalImagePaths;
        }

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $product->update($data);

        // Transform response similar to show/index and include sold/stock_left
        $product->load('user');
        $sold = $product->orders()
            ->whereNotIn('orders.status', ['cancelled', 'refund_cancelled'])
            ->sum('order_product.quantity');
        $stockLeft = max(0, (int)$product->stock_quantity - (int)$sold);

        $transformed = [
            'user_id' => $product->user_id,
            'id' => $product->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'category' => $product->category,
            'brand' => $product->brand,
            'model' => $product->model,
            'price' => [
                'raw' => $product->price,
                'formatted' => '₹' . number_format($product->price, 2),
                'mrp' => [
                    'raw' => $product->mrp,
                    'formatted' => '₹' . number_format($product->mrp, 2)
                ],
                'discount_percentage' => $product->discount_percentage,
                'savings' => [
                    'raw' => $product->savings ?? null,
                    'formatted' => isset($product->savings) ? ('₹' . number_format($product->savings, 2)) : null,
                    'percentage' => $product->savings_percentage ?? null
                ]
            ],
            'stock_quantity' => $product->stock_quantity,
            'sold_quantity' => (int)$sold,
            'stock_left' => (int)$stockLeft,
            'specifications' => $product->specifications,
            'highlights' => $product->highlights,
            'images' => [
                'main' => $product->main_image ? asset('storage/' . $product->main_image) : null,
                'additional' => collect($product->additional_images)->map(function($image) {
                    return asset('storage/' . $image);
                })
            ],
            'is_featured' => $product->is_featured,
            'is_active' => $product->is_active,
            'ratings' => [
                'average' => $product->average_rating,
                'total' => $product->review_count
            ],
            'shipping' => [
                'dimensions' => [
                    'weight' => $product->weight,
                    'length' => $product->length,
                    'width' => $product->width,
                    'height' => $product->height
                ],
                'free_shipping' => $product->free_shipping
            ],
            'vendor' => $product->user ? [
                'id' => $product->user->id,
                'name' => $product->user->name,
                'email' => $product->user->email
            ] : null,
            'created_at' => [
                'raw' => $product->created_at,
                'formatted' => $product->created_at->format('d M Y, h:i A')
            ]
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Product updated successfully',
            'data' => $transformed
        ]);
    }

    public function destroy(Product $product)
    {
        // Check if user can delete this product
        if (auth()->user()->hasRole('vendor') && $product->user_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to delete this product'
            ], 403);
        }

        // Delete product images
        if ($product->main_image) {
            Storage::disk('public')->delete($product->main_image);
        }
        
        foreach ($product->additional_images as $image) {
            Storage::disk('public')->delete($image);
        }

        $product->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Product deleted successfully'
        ]);
    }

    public function show(Product $product)
    {
        $product->load(['user', 'reviews.user']);

        // Calculate sold quantity and left stock
        $sold = $product->orders()
            ->whereNotIn('orders.status', ['cancelled', 'refund_cancelled'])
            ->sum('order_product.quantity');
        $stockLeft = max(0, (int)$product->stock_quantity - (int)$sold);

        return response()->json([
            'status' => 'success',
            'data' => [
                'user_id' => $product->user_id,
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'category' => $product->category,
                'brand' => $product->brand,
                'model' => $product->model,
                'price' => [
                    'raw' => $product->price,
                    'formatted' => '₹' . number_format($product->price, 2),
                    'mrp' => [
                        'raw' => $product->mrp,
                        'formatted' => '₹' . number_format($product->mrp, 2)
                    ],
                    'discount_percentage' => $product->discount_percentage,
                    'savings' => [
                        'raw' => $product->savings,
                        'formatted' => '₹' . number_format($product->savings, 2),
                        'percentage' => $product->savings_percentage
                    ]
                ],
                'stock_quantity' => $product->stock_quantity,
                'sold_quantity' => (int)$sold,
                'stock_left' => (int)$stockLeft,
                'specifications' => $product->specifications,
                'highlights' => $product->highlights,
                'images' => [
                    'main' => $product->main_image ? asset('storage/' . $product->main_image) : null,
                    'additional' => collect($product->additional_images)->map(function($image) {
                        return asset('storage/' . $image);
                    })
                ],
                'is_featured' => $product->is_featured,
                'is_active' => $product->is_active,
                'ratings' => [
                    'average' => $product->average_rating,
                    'total' => $product->review_count,
                    'reviews' => $product->reviews->map(function($review) {
                        return [
                            'id' => $review->id,
                            'rating' => $review->rating,
                            'comment' => $review->comment,
                            'pros' => $review->pros,
                            'cons' => $review->cons,
                            'images' => collect($review->images)->map(function($image) {
                                return asset('storage/' . $image);
                            }),
                            'is_verified_purchase' => $review->is_verified_purchase,
                            'helpful_votes' => $review->helpful_votes,
                            'user' => [
                                'id' => $review->user->id,
                                'name' => $review->user->name
                            ],
                            'created_at' => [
                                'raw' => $review->created_at,
                                'formatted' => $review->created_at->format('d M Y')
                            ]
                        ];
                    })
                ],
                'shipping' => [
                    'dimensions' => [
                        'weight' => $product->weight,
                        'length' => $product->length,
                        'width' => $product->width,
                        'height' => $product->height
                    ],
                    'free_shipping' => $product->free_shipping
                ],
                'vendor' => [
                    'id' => $product->user->id,
                    'name' => $product->user->name,
                    'email' => $product->user->email
                ],
                'created_at' => [
                    'raw' => $product->created_at,
                    'formatted' => $product->created_at->format('d M Y, h:i A')
                ]
            ]
        ]);
    }
}