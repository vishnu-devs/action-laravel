<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Manage Vendors
        </h2>
    </x-slot>
    <x-admin-sidebar>
        <div>
            <h3 class="text-lg font-bold mb-4">Vendors List</h3>
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b">ID</th>
                        <th class="py-2 px-4 border-b">Name</th>
                        <th class="py-2 px-4 border-b">Email</th>
                        <th class="py-2 px-4 border-b">Company</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $vendors = \App\Models\User::role('vendor')->with('company')->get();
                    @endphp
                    @forelse($vendors as $vendor)
                        <tr>
                            <td class="py-2 px-4 border-b">{{ $vendor->id }}</td>
                            <td class="py-2 px-4 border-b">{{ $vendor->name }}</td>
                            <td class="py-2 px-4 border-b">{{ $vendor->email }}</td>
                            <td class="py-2 px-4 border-b">{{ $vendor->company->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-2 px-4 text-center">No vendors found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin-sidebar>
</x-app-layout>
