<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Users and Roles Management Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h2 class="text-2xl font-bold mb-4">Users and Roles</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="px-4 py-2 text-left">User Name</th>
                                <th class="px-4 py-2 text-left">Current Role</th>
                                <th class="px-4 py-2 text-left">Assign Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center">
                                        <div class="text-sm">
                                            <p class="font-semibold text-gray-900">{{ $user->name }}</p>
                                            <p class="text-gray-600">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $user->roles->isNotEmpty() ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $user->roles->pluck('name')->implode(', ') ?: 'No role' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <form action="{{ route('admin.roles-permissions.assign-role') }}" method="POST" class="inline-block mr-2">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                                        <div class="flex items-center gap-2">
                                            <select name="role" required class="rounded-md border-gray-300 text-base">
                                                <option value="">Select role</option>
                                                @foreach($roles as $role)
                                                    <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                                                        {{ ucfirst($role->name) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium py-2 px-4 rounded-md transition-colors duration-150">
                                                Update Role
                                            </button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Role Permissions Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h2 class="text-2xl font-bold mb-4">Role Permissions</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="px-4 py-2 text-left">Role</th>
                                <th class="px-4 py-2 text-left">Current Permissions</th>
                                <th class="px-4 py-2 text-left">Manage Permissions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roles as $role)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <span class="font-semibold text-gray-900">{{ ucfirst($role->name) }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($role->permissions as $permission)
                                            <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                                {{ $permission->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <form action="{{ route('admin.roles-permissions.assign-permission') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="role" value="{{ $role->name }}">
                                        <div class="space-y-2">
                                            <div class="flex flex-wrap gap-3">
                                                @foreach($permissions as $permission)
                                                <label class="inline-flex items-center">
                                                    <input type="checkbox" 
                                                        name="permissions[]" 
                                                        value="{{ $permission->name }}"
                                                        {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}
                                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                                    <span class="ml-2 text-sm text-gray-700">{{ $permission->name }}</span>
                                                </label>
                                                @endforeach
                                            </div>
                                            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white text-sm font-medium py-2 px-4 rounded-md transition-colors duration-150">
                                                Update Permissions
                                            </button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Role and Permission Management Section -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Create Role Section -->
                    <div>
                        <h2 class="text-xl font-bold mb-4">Create New Role</h2>
                        <form action="{{ route('admin.roles-permissions.create-role') }}" method="POST">
                            @csrf
                            <div class="flex gap-4">
                                <input type="text" name="name" placeholder="Enter role name" required
                                    class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block w-full">
                                <button type="submit"
                                    class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium py-2 px-4 rounded-md transition-colors duration-150">
                                    Create Role
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Create Permission Section -->
                    <div>
                        <h2 class="text-xl font-bold mb-4">Create New Permission</h2>
                        <form action="{{ route('admin.roles-permissions.create-permission') }}" method="POST">
                            @csrf
                            <div class="flex gap-4">
                                <input type="text" name="name" placeholder="Enter permission name" required
                                    class="rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 block w-full">
                                <button type="submit"
                                    class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium py-2 px-4 rounded-md transition-colors duration-150">
                                    Create Permission
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>