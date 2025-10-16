<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Vendor Request Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <a href="{{ route('admin.vendor-requests.index') }}" class="text-indigo-600 hover:text-indigo-900">← Back to Vendor Requests</a>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold mb-4">Business Information</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <dl class="grid grid-cols-1 gap-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Business Name</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $vendorRequest->business_name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Business Type</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $vendorRequest->business_type }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">GST Number</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $vendorRequest->gst_number }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">PAN Number</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $vendorRequest->pan_number }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Address</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $vendorRequest->address }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold mb-4">User Information</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <dl class="grid grid-cols-1 gap-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Name</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $vendorRequest->user->name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $vendorRequest->user->email }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Request Status</dt>
                                        <dd class="mt-1">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($vendorRequest->status === 'approved') bg-green-100 text-green-800 
                                                @elseif($vendorRequest->status === 'rejected') bg-red-100 text-red-800 
                                                @else bg-yellow-100 text-yellow-800 
                                                @endif">
                                                {{ ucfirst($vendorRequest->status) }}
                                            </span>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Request Date</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ $vendorRequest->created_at->format('d M Y, h:i A') }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>

                    @if($vendorRequest->documents->count() > 0)
                        <div class="mt-8">
                            <h3 class="text-lg font-semibold mb-4">Documents</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($vendorRequest->documents as $document)
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <a href="{{ asset('storage/' . $document->path) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900">
                                            View Document {{ $loop->iteration }}
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($vendorRequest->status === 0)
                        <div class="mt-8 flex space-x-4">
                            <form method="POST" action="{{ route('admin.vendor-requests.update', $vendorRequest) }}" class="inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="approved">
                                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                    Approve Request
                                </button>
                            </form>

                            <form method="POST" action="{{ route('admin.vendor-requests.update', $vendorRequest) }}" class="inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                    Reject Request
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>