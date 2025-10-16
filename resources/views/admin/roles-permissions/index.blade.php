<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Roles & Permissions Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <!-- Create Role Form -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold mb-4">Create New Role</h3>
                    <form action="{{ route('admin.roles-permissions.create-role') }}" method="POST" class="flex gap-4">
                        @csrf
                        <input type="text" name="name" placeholder="Role name" required
                            class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <button type="submit"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Create Role
                        </button>
                    </form>
                </div>

                <!-- Create Permission Form -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold mb-4">Create New Permission</h3>
                    <form action="{{ route('admin.roles-permissions.create-permission') }}" method="POST" class="flex gap-4">
                        @csrf
                        <input type="text" name="name" placeholder="Permission name" required
                            class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <button type="submit"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Create Permission
                        </button>
                    </form>
                </div>

                <!-- Roles List -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold mb-4">Roles and Their Permissions</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Role
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Permissions
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($roles as $role)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            {{ $role->name }}
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($role->permissions as $permission)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                        {{ $permission->name }}
                                                        <form action="{{ route('admin.roles-permissions.remove-permission') }}" method="POST" class="inline ml-1">
                                                            @csrf
                                                            <input type="hidden" name="role_id" value="{{ $role->id }}">
                                                            <input type="hidden" name="permission" value="{{ $permission->name }}">
                                                            <button type="submit" class="text-red-500 hover:text-red-700">×</button>
                                                        </form>
                                                    </span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <form action="{{ route('admin.roles-permissions.assign-permission') }}" method="POST" class="flex gap-2">
                                                @csrf
                                                <input type="hidden" name="role_id" value="{{ $role->id }}">
                                                <select name="permission" required
                                                    class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                                    <option value="">Select Permission</option>
                                                    @foreach($permissions as $permission)
                                                        @if(!$role->hasPermissionTo($permission))
                                                            <option value="{{ $permission->name }}">{{ $permission->name }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                <button type="submit"
                                                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded text-sm">
                                                    Add
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Assign Role to User Form -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold mb-4">Assign Role to User</h3>
                    <form action="{{ route('admin.roles-permissions.assign-role') }}" method="POST" class="flex gap-4">
                        @csrf
                        <input type="text" name="user_id" placeholder="User ID" required
                            class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <select name="role" required
                            class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Assign Role
                        </button>
                    </form>
                </div>

                <!-- Remove Role from User Form -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Remove Role from User</h3>
                    <form action="{{ route('admin.roles-permissions.remove-role') }}" method="POST" class="flex gap-4">
                        @csrf
                        <input type="text" name="user_id" placeholder="User ID" required
                            class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <select name="role" required
                            class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                        <button type="submit"
                            class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                            Remove Role
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>