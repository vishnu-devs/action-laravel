<!DOCTYPE html>
<html>
<body>
    <h2>New Order Includes Your Products</h2>
    <p>Hi {{ $vendor->name }},</p>
    <p>Order #{{ $order->id }} placed by {{ $order->user->name }} includes your products.</p>
    <h3>Items for you</h3>
    <ul>
        @foreach($order->products->where('user_id', $vendor->id) as $product)
            <li>{{ $product->name }} × {{ $product->pivot->quantity }}</li>
        @endforeach
    </ul>
    <p>Please prepare for shipment.</p>
</body>
</html>