<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Manage Users
        </h2>
    </x-slot>
    <x-admin-sidebar>
        <div>
            <h3 class="text-lg font-bold mb-4">Users List</h3>
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
                        $users = \App\Models\User::with('company')->get();
                    @endphp
                    @forelse($users as $user)
                        <tr>
                            <td class="py-2 px-4 border-b">{{ $user->id }}</td>
                            <td class="py-2 px-4 border-b">{{ $user->name }}</td>
                            <td class="py-2 px-4 border-b">{{ $user->email }}</td>
                            <td class="py-2 px-4 border-b">{{ $user->company->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-2 px-4 text-center">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin-sidebar>
</x-app-layout>
