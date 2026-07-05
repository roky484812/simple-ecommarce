<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Admin') - {{ config('app.name', 'Laravel') }}</title>

    @fonts

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 antialiased">

    <div class="flex min-h-screen">
        <!-- Sidebar (off-canvas below lg) -->
        <aside
            id="admin-sidebar"
            class="hs-overlay hs-overlay-open:translate-x-0 -translate-x-full lg:translate-x-0 fixed lg:static top-0 start-0 z-90 h-full lg:h-auto w-64 shrink-0 bg-gray-900 text-gray-100 transition-all duration-300 transform"
            role="dialog"
            tabindex="-1"
        >
            <div class="flex items-center justify-between px-4 py-4 border-b border-gray-800">
                <a href="{{ Route::has('admin.dashboard') ? route('admin.dashboard') : '#' }}" class="text-lg font-bold text-white">
                    {{ config('app.name', 'Laravel') }} <span class="text-gray-400 font-normal">Admin</span>
                </a>
                <button
                    type="button"
                    class="lg:hidden inline-flex items-center justify-center rounded-lg p-2 text-gray-300 hover:bg-gray-800 focus:outline-none focus:ring-4 focus:ring-gray-700"
                    aria-label="Close sidebar"
                    data-hs-overlay="#admin-sidebar"
                >
                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg>
                </button>
            </div>

            <nav class="flex flex-col gap-1 p-4">
                <a href="{{ Route::has('admin.dashboard') ? route('admin.dashboard') : '#' }}" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-200 hover:bg-gray-800">Dashboard</a>
                <a href="{{ Route::has('admin.categories.index') ? route('admin.categories.index') : '#' }}" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-200 hover:bg-gray-800">Categories</a>
                <a href="{{ Route::has('admin.products.index') ? route('admin.products.index') : '#' }}" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-200 hover:bg-gray-800">Products</a>
                <a href="{{ Route::has('admin.orders.index') ? route('admin.orders.index') : '#' }}" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-200 hover:bg-gray-800">Orders</a>
                <a href="{{ Route::has('admin.customers.index') ? route('admin.customers.index') : '#' }}" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-200 hover:bg-gray-800">Customers</a>
                <a href="{{ Route::has('admin.payments.index') ? route('admin.payments.index') : '#' }}" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-200 hover:bg-gray-800">Payments</a>
            </nav>
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            <!-- Topbar -->
            <header class="sticky top-0 z-50 bg-white border-b border-gray-200">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6">
                    <button
                        type="button"
                        class="lg:hidden inline-flex items-center justify-center rounded-lg p-2 text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-gray-200"
                        aria-label="Toggle sidebar"
                        data-hs-overlay="#admin-sidebar"
                    >
                        <svg class="size-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12h16" /><path d="M4 6h16" /><path d="M4 18h16" /></svg>
                    </button>

                    <div class="flex-1"></div>

                    <div class="flex items-center gap-3">
                        @auth
                            <span class="text-sm font-medium text-gray-700">{{ Auth::user()->name }}</span>
                            <form method="POST" action="{{ Route::has('logout') ? route('logout') : '#' }}">
                                @csrf
                                <x-ui.button type="submit" variant="ghost" size="sm">Log out</x-ui.button>
                            </form>
                        @endauth
                    </div>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @yield('content')
            </main>
        </div>
    </div>

</body>
</html>
