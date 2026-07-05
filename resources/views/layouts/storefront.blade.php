<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', config('app.name', 'Laravel'))</title>

    @fonts

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 antialiased">

    <header class="sticky top-0 z-50 bg-white border-b border-gray-200">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-4">
                    <button
                        type="button"
                        class="lg:hidden inline-flex items-center justify-center rounded-lg p-2 text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-gray-200"
                        aria-label="Toggle navigation"
                        data-hs-overlay="#storefront-offcanvas-nav"
                    >
                        <svg class="size-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12h16" /><path d="M4 6h16" /><path d="M4 18h16" /></svg>
                    </button>

                    <a href="{{ route('home') }}" class="text-xl font-bold text-brand-700">
                        {{ config('app.name', 'Laravel') }}
                    </a>
                </div>

                <div class="hidden lg:flex items-center gap-6">
                    <a href="{{ route('home') }}" class="text-sm font-medium text-gray-700 hover:text-brand-700">Home</a>
                    <a href="{{ Route::has('products.index') ? route('products.index') : '#' }}" class="text-sm font-medium text-gray-700 hover:text-brand-700">Products</a>
                </div>

                <div class="flex items-center gap-2">
                    <a
                        href="{{ Route::has('cart.index') ? route('cart.index') : '#' }}"
                        class="relative inline-flex items-center justify-center rounded-lg p-2 text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-gray-200"
                        aria-label="Cart"
                    >
                        <svg class="size-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1" /><circle cx="19" cy="21" r="1" /><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" /></svg>
                    </a>

                    @auth
                        <a href="{{ Route::has('profile.edit') ? route('profile.edit') : '#' }}" class="text-sm font-medium text-gray-700 hover:text-brand-700 px-2">
                            {{ Auth::user()->name }}
                        </a>
                    @else
                        <x-ui.button as="a" :href="Route::has('login') ? route('login') : '#'" variant="ghost" size="sm">Log in</x-ui.button>
                        <x-ui.button as="a" :href="Route::has('register') ? route('register') : '#'" variant="primary" size="sm">Register</x-ui.button>
                    @endauth
                </div>
            </div>
        </nav>
    </header>

    <!-- Off-canvas mobile navigation -->
    <div
        id="storefront-offcanvas-nav"
        class="hs-overlay hs-overlay-open:translate-x-0 -translate-x-full fixed top-0 start-0 transition-all duration-300 transform h-full max-w-xs w-full z-90 bg-white border-e border-gray-200"
        role="dialog"
        tabindex="-1"
    >
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
            <span class="text-lg font-bold text-brand-700">{{ config('app.name', 'Laravel') }}</span>
            <button
                type="button"
                class="inline-flex items-center justify-center rounded-lg p-2 text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-gray-200"
                aria-label="Close navigation"
                data-hs-overlay="#storefront-offcanvas-nav"
            >
                <svg class="size-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg>
            </button>
        </div>
        <nav class="flex flex-col gap-1 p-4">
            <a href="{{ route('home') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">Home</a>
            <a href="{{ Route::has('products.index') ? route('products.index') : '#' }}" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">Products</a>
        </nav>
    </div>

    <main>
        @yield('content')
    </main>

    <footer class="bg-white border-t border-gray-200 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            @hasSection('footer')
                @yield('footer')
            @else
                <p class="text-sm text-gray-500 text-center">
                    &copy; {{ now()->year }} {{ config('app.name', 'Laravel') }}. All rights reserved.
                </p>
            @endif
        </div>
    </footer>

</body>
</html>
