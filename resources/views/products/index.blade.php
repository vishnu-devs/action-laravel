<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Products') }}
            </h2>
            @can('products.create')
            <a href="{{ route('products.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Add New Product
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if($products->isEmpty())
                        <p class="text-center text-gray-500">No products found.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($products as $product)
                                        <tr class="hover:bg-gray-50 {{ $product->trashed() ? 'bg-red-50' : '' }}">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $product->id }}
                                                @if($product->trashed())
                                                    <span class="text-red-600 text-xs">(Deleted)</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $product->name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">₹{{ number_format($product->price, 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $product->user->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $product->user->email }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $product->created_at->format('d M Y, h:i A') }}
                                                @if($product->trashed())
                                                    <br>
                                                    <span class="text-red-600 text-xs">Deleted at: {{ $product->deleted_at->format('d M Y, h:i A') }}</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                                @role('super-admin')
                                                    @if($product->trashed())
                                                        <form action="{{ route('products.restore', $product->id) }}" method="POST" class="inline">
                                                            @csrf
                                                            <button type="submit" class="text-green-600 hover:text-green-900">
                                                                Restore
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('products.force-delete', $product->id) }}" method="POST" class="inline ml-2">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to permanently delete this product?')">
                                                                Delete Permanently
                                                            </button>
                                                        </form>
                                                    @else
                                                        <a href="{{ route('products.edit', $product) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                                        <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline ml-2">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this product?')">
                                                                Delete
                                                            </button>
                                                        </form>
                                                    @endif
                                                @else
                                                    @can('products.edit')
                                                        <a href="{{ route('products.edit', $product) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                                    @endcan
                                                @endrole
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-6">
                            {{ $products->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>