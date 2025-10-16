<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Vendor Requests
        </h2>
    </x-slot>
    <x-admin-sidebar>
        <div>
            <h3 class="text-lg font-bold mb-4">Vendor Requests</h3>
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b">ID</th>
                        <th class="py-2 px-4 border-b">Name</th>
                        <th class="py-2 px-4 border-b">Email</th>
                        <th class="py-2 px-4 border-b">Company</th>
                        <th class="py-2 px-4 border-b">Status</th>
                        <th class="py-2 px-4 border-b">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vendorRequests as $vendorRequest) <!-- $vendorRequests is passed from AdminVendorRequestController@index -->
                        <tr>
                            <td class="py-2 px-4 border-b">{{ $vendorRequest->id }}</td>
                            <td class="py-2 px-4 border-b">{{ $vendorRequest->user->name }}</td>
                            <td class="py-2 px-4 border-b">{{ $vendorRequest->user->email }}</td>
                            <td class="py-2 px-4 border-b">{{ $vendorRequest->business_name }}</td>
                            <td class="py-2 px-4 border-b">
                                @if($vendorRequest->status === 0)
                                    Pending
                                @elseif($vendorRequest->status === 1)
                                    Approved
                                @else
                                    Rejected
                                @endif
                            </td>
                            <td class="py-2 px-4 border-b">
                                @if($vendorRequest->status === 0)
                                    <form action="{{ route('admin.vendor-requests.update', $vendorRequest->id) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="1">
                                        <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-2 rounded">Accept</button>
                                    </form>
                                    <form action="{{ route('admin.vendor-requests.update', $vendorRequest->id) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="2">
                                        <button type="submit" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-2 rounded">Decline</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-2 px-4 text-center">No vendor requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin-sidebar>
</x-app-layout>
