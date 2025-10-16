<div class="flex min-h-screen">
    <!-- Sidebar -->
    <aside class="w-64 bg-white shadow-md p-6">
        <ul class="space-y-4">
            <li><a href="/admin/users" class="text-blue-600 hover:underline">Manage Users</a></li>
            <li><a href="/admin/vendors" class="text-blue-600 hover:underline">Manage Vendors</a></li>
            <li><a href="/admin/products" class="text-blue-600 hover:underline">Manage Products</a></li>
            <li><a href="/admin/orders" class="text-blue-600 hover:underline">Manage Orders</a></li>
            <li><a href="/admin/vendor-requests" class="text-blue-600 hover:underline">Vendor Requests</a></li>
            @role('super_admin')
            <li><a href="/admin/roles-permissions" class="text-blue-600 hover:underline">Roles &amp; Permissions</a></li>
            @endrole
        </ul>
    </aside>
    <!-- Main Content -->
    <main class="flex-1 p-8 bg-gray-50 px-6 pt-6">
        {{ $slot }}
    </main>
</div>
