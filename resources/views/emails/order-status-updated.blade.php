<!DOCTYPE html>
<html>
<body>
    <h2>Order Status Update</h2>
    <p>Hi {{ $recipientName ?? $order->user->name }},</p>
    <p>Your order #{{ $order->id }} status has been updated to <strong>{{ $statusLabel }}</strong>.</p>
    @if(strtolower($statusLabel) === 'cancelled')
        <p>Your order has been cancelled. If you did not request this, please contact our support team.</p>
    @endif
    <h3>Items</h3>
    <ul>
        @foreach($order->products as $product)
            <li>{{ $product->name }} × {{ $product->pivot->quantity }} — ₹{{ number_format($product->price, 2) }}</li>
        @endforeach
    </ul>
    <p>Total: ₹{{ number_format($order->total_amount, 2) }}</p>
    <p>We will keep you posted on further updates.</p>
    <p>Thank you for shopping with us.</p>
</body>
</html>