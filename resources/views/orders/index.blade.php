<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Orders') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($orders->isEmpty())
                        <p class="text-center text-gray-500">No orders found.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Products</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        @role('vendor')
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        @endrole
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($orders as $order)
                                        @php
                                            $totalQty = $order->products->sum(fn($p) => $p->pivot->quantity);
                                            $computedTotal = $order->products->sum(fn($p) => $p->price * $p->pivot->quantity);
                                            $amount = $order->total_amount ?? $computedTotal;
                                            $labels = ['processing' => 'Accepted', 'shipped' => 'Dispatched', 'delivered' => 'Delivered', 'cancelled' => 'Rejected'];
                                            $statusLabel = $labels[$order->status] ?? ucfirst($order->status ?? 'processing');
                                            $statusClasses = [
                                                'Accepted' => 'bg-blue-100 text-blue-800',
                                                'Dispatched' => 'bg-yellow-100 text-yellow-800',
                                                'Delivered' => 'bg-green-100 text-green-800',
                                                'Rejected' => 'bg-red-100 text-red-800',
                                            ];
                                        @endphp
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#{{ $order->id }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    @if($order->products && $order->products->count())
                                                        <ul class="list-disc list-inside">
                                                            @foreach($order->products as $product)
                                                                <li>{{ $product->name }} × {{ $product->pivot->quantity }}</li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        -
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $totalQty }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₹{{ number_format($amount, 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClasses[$statusLabel] ?? 'bg-gray-100 text-gray-800' }}">
                                                    {{ $statusLabel }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order->created_at->format('d M Y, h:i A') }}</td>
                                            @role('vendor')
                                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                    <a href="{{ route('orders.edit', $order) }}" class="text-indigo-600 hover:text-indigo-900">Update Status</a>
                                                </td>
                                            @endrole
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">
                            {{ $orders->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>