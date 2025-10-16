<!DOCTYPE html>
<html>
<body>
    <h2>Your Order is Confirmed</h2>
    <p>Hi {{ $recipientName ?? $order->user->name }},</p>
    <p>Order #{{ $order->id }} has been placed successfully.</p>
    <p>Total: ₹{{ number_format($order->total_amount, 2) }}</p>
    <h3>Items</h3>
    <ul>
        @foreach($order->products as $product)
            <li>{{ $product->name }} × {{ $product->pivot->quantity }} — ₹{{ number_format($product->price, 2) }}</li>
        @endforeach
    </ul>
    @if(!empty($note))
        <p><strong>Note:</strong> {{ $note }}</p>
    @endif
    <p>We will update you as the order progresses.</p>
</body>
</html>