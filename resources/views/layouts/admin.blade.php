<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Admin') - {{ config('app.name', 'Laravel') }}</title>

    @fonts

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>
<body class="bg-gray-50 text-gray-900 antialiased">

    <div class="flex min-h-screen">
        <!-- Sidebar (off-canvas below lg) -->
        <aside
            id="admin-sidebar"
            class="hs-overlay hs-overlay-open:translate-x-0 -translate-x-full lg:translate-x-0 fixed lg:sticky top-0 start-0 z-90 h-full lg:h-screen w-64 shrink-0 bg-gray-900 text-gray-100 transition-all duration-300 transform flex flex-col"
            role="dialog"
            tabindex="-1"
        >
            <div class="flex items-center justify-between px-4 py-4 border-b border-gray-800 shrink-0">
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

            <nav class="flex flex-col gap-1 p-4 overflow-y-auto flex-1">
                <a href="{{ Route::has('admin.dashboard') ? route('admin.dashboard') : '#' }}" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-200 hover:bg-gray-800">Dashboard</a>
                <a href="{{ Route::has('admin.categories.index') ? route('admin.categories.index') : '#' }}" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-200 hover:bg-gray-800">Categories</a>
                <a href="{{ Route::has('admin.products.index') ? route('admin.products.index') : '#' }}" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-200 hover:bg-gray-800">Products</a>
                <a href="{{ Route::has('admin.orders.index') ? route('admin.orders.index') : '#' }}" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-200 hover:bg-gray-800">Orders</a>
                <a href="{{ Route::has('admin.customers.index') ? route('admin.customers.index') : '#' }}" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-200 hover:bg-gray-800">Customers</a>
                <a href="{{ Route::has('admin.payments.index') ? route('admin.payments.index') : '#' }}" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-200 hover:bg-gray-800">Payments</a>
            </nav>

            @auth
                <div class="shrink-0 p-4 border-t border-gray-800">
                    <form method="POST" action="{{ Route::has('logout') ? route('logout') : '#' }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-gray-200 hover:bg-gray-800 text-left">
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" /><polyline points="16 17 21 12 16 7" /><line x1="21" x2="9" y1="12" y2="12" /></svg>
                            Log out
                        </button>
                    </form>
                </div>
            @endauth
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
                        @endauth
                    </div>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @if (session('success'))
                    <x-ui.alert variant="success" class="mb-4">{{ session('success') }}</x-ui.alert>
                @endif

                @if (session('error'))
                    <x-ui.alert variant="error" class="mb-4">{{ session('error') }}</x-ui.alert>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
