<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderPlacedMail;
use App\Mail\VendorOrderNotificationMail;
use App\Mail\OrderStatusUpdatedMail;

class OrderController extends Controller
{
    public function index()
    {
        $query = Order::with(['user', 'products.user']);
        
        // If user is vendor, only show orders containing their products
        if (auth()->user()->hasRole('vendor')) {
            $query->whereHas('products', function($q) {
                $q->where('user_id', auth()->id());
            });
        }
        // If user is customer (not admin/super_admin/vendor), only show their own orders
        elseif (!auth()->user()->hasRole('admin') && !auth()->user()->hasRole('super_admin')) {
            $query->where('user_id', auth()->id());
        }
        
        $orders = $query->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'user' => [
                        'id' => $order->user->id,
                        'name' => $order->user->name,
                        'email' => $order->user->email
                    ],
                    'products' => $order->products->map(function ($product) {
                        return [
                            'id' => $product->id,
                            'name' => $product->name,
                            'price' => [
                                'raw' => $product->price,
                                'formatted' => '₹' . number_format($product->price, 2)
                            ],
                            'quantity' => $product->pivot->quantity,
                            'vendor' => [
                                'id' => $product->user->id,
                                'name' => $product->user->name
                            ]
                        ];
                    }),
                    'shipping_address' => $order->shipping_address,
                    'payment_method' => $order->payment_method,
                    'total_amount' => [
                        'raw' => $order->total_amount,
                        'formatted' => '₹' . number_format($order->total_amount, 2)
                    ],
                    'status' => $order->status,
                    'created_at' => [
                        'raw' => $order->created_at,
                        'formatted' => $order->created_at->format('d M Y, h:i A')
                    ]
                ];
            }),
            'pagination' => [
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage()
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'shipping_address' => 'required|string',
            'payment_method' => 'required|in:cod,online',
            'contact_name' => 'sometimes|string|min:2',
            'contact_email' => 'sometimes|email',
            'contact_phone' => 'sometimes|string'
        ]);

        $totalAmount = 0;
        $products = [];

        foreach ($request->products as $item) {
            $product = Product::findOrFail($item['id']);
            $totalAmount += $product->price * $item['quantity'];
            $products[$item['id']] = ['quantity' => $item['quantity']];
        }

        $contactName = $request->input('contact_name', $request->user()->name);
        $contactEmail = $request->input('contact_email', $request->user()->email);
        $contactPhone = $request->input('contact_phone', $request->user()->phone ?? null);

        $order = Order::create([
            'user_id' => auth()->id(),
            'total_amount' => $totalAmount,
            'shipping_address' => $request->shipping_address,
            'payment_method' => $request->payment_method,
            'status' => 'processing',
            'contact_name' => $contactName,
            'contact_email' => $contactEmail,
            'contact_phone' => $contactPhone,
        ]);

        $order->products()->attach($products);

        // Reload order with relations for emails
        $order = $order->load(['user', 'products.user']);

        // Send confirmation to the email provided in form (or fallback to account)
        Mail::to($contactEmail)->send(new OrderPlacedMail(
            $order,
            'Order Confirmation',
            $contactName
        ));

        // Notify the logged-in account that this email placed the order
        $accountName = $request->user()->name;
        $accountEmail = $request->user()->email;
        $note = "This order was placed using contact email: $contactEmail and name: $contactName.";
        Mail::to($accountEmail)->send(new OrderPlacedMail(
            $order,
            "Order placed by $contactEmail",
            $accountName,
            $note
        ));

        // Notify unique vendors involved in this order
        $vendors = $order->products->pluck('user')->unique('id');
        foreach ($vendors as $vendor) {
            if (!empty($vendor->email)) {
                Mail::to($vendor->email)->send(new VendorOrderNotificationMail($order, $vendor));
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Order placed successfully',
            'data' => $order
        ], 201);
    }

    public function show(Order $order)
    {
        // Check if user is authorized to view this order
        if (auth()->id() !== $order->user_id && 
            !auth()->user()->hasRole('admin') && 
            !$order->products()->where('user_id', auth()->id())->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $order->id,
                'user' => [
                    'id' => $order->user->id,
                    'name' => $order->user->name,
                    'email' => $order->user->email
                ],
                'products' => $order->products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'price' => [
                            'raw' => $product->price,
                            'formatted' => '₹' . number_format($product->price, 2)
                        ],
                        'quantity' => $product->pivot->quantity,
                        'vendor' => [
                            'id' => $product->user->id,
                            'name' => $product->user->name
                        ]
                    ];
                }),
                'total_amount' => [
                    'raw' => $order->total_amount,
                    'formatted' => '₹' . number_format($order->total_amount, 2)
                ],
                'shipping_address' => $order->shipping_address,
                'payment_method' => $order->payment_method,
                'status' => $order->status,
                'created_at' => [
                    'raw' => $order->created_at,
                    'formatted' => $order->created_at->format('d M Y, h:i A')
                ]
            ]
        ]);
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:processing,shipped,delivered,cancelled,return_requested,returned,refunded,refund_cancelled'
        ]);

        $user = auth()->user();

        $isAdmin = $user->hasRole('admin');
        $isVendorOnOrder = $order->products()->where('user_id', $user->id)->exists();
        $isCustomerOwner = $order->user_id === $user->id;

        // Customer can request cancel (before delivered), return (after delivered), mark refunded (after cancel/return), or cancel refund (after cancel/return/refund)
        if ($isCustomerOwner && in_array($request->status, ['cancelled', 'return_requested', 'refunded', 'refund_cancelled'])) {
            // Guard transitions
            if ($request->status === 'cancelled' && $order->status === 'delivered') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order already delivered; cannot cancel.'
                ], 422);
            }
            if ($request->status === 'return_requested' && $order->status !== 'delivered') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Return can be requested only after delivery.'
                ], 422);
            }
            if ($request->status === 'refunded' && !in_array($order->status, ['cancelled','returned'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Refund can be processed only after cancellation or return.'
                ], 422);
            }
            if ($request->status === 'refund_cancelled' && !in_array($order->status, ['cancelled','returned','refunded'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Refund can be cancelled only after cancellation/return or a refund in progress.'
                ], 422);
            }
        } else {
            // Otherwise require admin or vendor on order
            if (!$isAdmin && !$isVendorOnOrder) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized'
                ], 403);
            }
            // Vendor/Admin can mark returned only after customer requested return
            if ($request->status === 'returned' && $order->status !== 'return_requested') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Return can be confirmed only after a customer return request.'
                ], 422);
            }
            // Vendor/Admin can mark refunded only after cancellation or return
            if ($request->status === 'refunded' && !in_array($order->status, ['cancelled','returned'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Refund can be processed only after cancellation or return.'
                ], 422);
            }
            // Vendor/Admin can cancel refund only after cancellation/return or a refund in progress
            if ($request->status === 'refund_cancelled' && !in_array($order->status, ['cancelled','returned','refunded'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Refund can be cancelled only after cancellation/return or a refund in progress.'
                ], 422);
            }
        }

        $order->update([
            'status' => $request->status,
            'status_updated_at' => now(),
        ]);

        // Send email to customer after status update (including cancellation)
        try {
            $order = $order->load(['user', 'products.user']);
            $recipientName = $order->contact_name ?? $order->user->name;
            $recipientEmail = $order->contact_email ?? $order->user->email;
            Mail::to($recipientEmail)
                ->send(new OrderStatusUpdatedMail($order, $request->status, $recipientName));

            // Also notify the logged-in account if different from contact email
            $accountName = $order->user->name;
            $accountEmail = $order->user->email;
            if (!empty($accountEmail) && strcasecmp($accountEmail, $recipientEmail) !== 0) {
                Mail::to($accountEmail)
                    ->send(new OrderStatusUpdatedMail($order, $request->status, $accountName));
            }

            // Notify unique vendors involved in this order about status change
            $vendors = $order->products->pluck('user')->unique('id');
            foreach ($vendors as $vendor) {
                if (!empty($vendor->email)) {
                    Mail::to($vendor->email)
                        ->send(new OrderStatusUpdatedMail($order, $request->status, $vendor->name));
                }
            }
        } catch (\Throwable $e) {
            \Log::error('Failed to send order status update email (API): ' . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Order status updated successfully',
            'data' => $order
        ]);
    }

    public function destroy(Order $order)
    {
        // Only admin can delete orders
        if (!auth()->user()->hasRole('admin')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $order->products()->detach();
        $order->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Order deleted successfully'
        ]);
    }
}