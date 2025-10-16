<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Update Order Status') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <a href="{{ route('orders.index') }}" class="text-indigo-600 hover:text-indigo-900">← {{ __('Back to Orders') }}</a>
                    </div>

                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">Order #{{ $order->id }}</h3>
                        <div class="bg-gray-50 p-4 rounded">
                            <h4 class="text-sm font-medium mb-2">Items</h4>
                            @if($order->products && $order->products->count())
                                <ul class="list-disc list-inside text-sm">
                                    @foreach($order->products as $product)
                                        <li>{{ $product->name }} × {{ $product->pivot->quantity }} — ₹{{ number_format($product->price, 2) }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-sm text-gray-500">No items found.</p>
                            @endif
                            <p class="mt-3 text-sm">Total: ₹{{ number_format($order->total_amount ?? $order->products->sum(fn($p) => $p->price * $p->pivot->quantity), 2) }}</p>
                        </div>
                    </div>

                    <form action="{{ route('orders.update', $order) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Select Status</label>
                            <select id="status" name="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="processing" @selected($order->status === 'processing')>Accepted</option>
                                <option value="shipped" @selected($order->status === 'shipped')>Dispatched</option>
                                <option value="delivered" @selected($order->status === 'delivered')>Delivered</option>
                                <option value="cancelled" @selected($order->status === 'cancelled')>Rejected</option>
                            </select>
                            @error('status')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex gap-3">
                            <x-primary-button>{{ __('Update Status') }}</x-primary-button>
                            <a href="{{ route('orders.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 bg-white hover:bg-gray-50">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>