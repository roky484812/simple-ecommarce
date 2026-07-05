<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', config('app.name', 'Laravel'))</title>

    @fonts

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
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
                    <a href="{{ route('products.index') }}" class="text-sm font-medium text-gray-700 hover:text-brand-700">Products</a>
                </div>

                <div class="flex items-center gap-2">
                    @php
                        $cartItemCount = app(\App\Services\CartService::class)->totalQty(auth()->user(), session()->getId());
                    @endphp
                    <button
                        type="button"
                        x-data
                        @click="$dispatch('open-cart-drawer')"
                        class="relative inline-flex items-center justify-center rounded-lg p-2 text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-gray-200"
                        aria-label="Cart{{ $cartItemCount > 0 ? " ({$cartItemCount} items)" : '' }}"
                    >
                        <svg class="size-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1" /><circle cx="19" cy="21" r="1" /><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" /></svg>
                        @if ($cartItemCount > 0)
                            <span class="absolute -top-1 -right-1 flex items-center justify-center min-w-[1.1rem] h-[1.1rem] px-1 rounded-full bg-brand-600 text-white text-[10px] font-semibold leading-none">
                                {{ $cartItemCount }}
                            </span>
                        @endif
                    </button>

                    @auth
                        <div class="dropdown dropdown-end">
                            <div tabindex="0" role="button" class="btn btn-ghost btn-sm gap-2 px-2">
                                <div class="avatar">
                                    <div class="w-7 rounded-full">
                                        <img src="{{ Auth::user()->avatarUrl() }}" alt="{{ Auth::user()->name }}" />
                                    </div>
                                </div>
                                <span class="hidden sm:inline">{{ Auth::user()->name }}</span>
                                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-50 w-52 p-2 shadow-sm border border-base-200">
                                <li><a href="{{ route('profile.edit') }}">Profile</a></li>
                                <li>
                                    <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Log Out</a>
                                </li>
                            </ul>
                            <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
                                @csrf
                            </form>
                        </div>
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
            <a href="{{ route('products.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">Products</a>
        </nav>
    </div>

    <main>
        @yield('content')
    </main>

    <livewire:storefront.cart-drawer />

    <x-ui.toast-container />

    @if (session('success') || session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        message: @json(session('success') ?? session('error')),
                        variant: @json(session('success') ? 'success' : 'error'),
                    },
                }));
            });
        </script>
    @endif

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

    @livewireScriptConfig
</body>
</html>
