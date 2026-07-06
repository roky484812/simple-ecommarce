<!DOCTYPE html>
@php $navCategories = \App\Models\Category::navigationTree(); @endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', \App\Models\Setting::get('app_name', config('app.name', 'Laravel')))</title>

    @fonts

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>
<body class="bg-gray-50 text-gray-900 antialiased">

    @if (session('impersonator_id'))
        <div class="bg-yellow-400 text-yellow-950 text-sm font-medium">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 flex items-center justify-between gap-3">
                <span>You are logged in as {{ Auth::user()->name }} (impersonating).</span>
                <form method="POST" action="{{ route('impersonate.stop') }}">
                    @csrf
                    <button type="submit" class="underline hover:no-underline">Return to admin account</button>
                </form>
            </div>
        </div>
    @endif

    <header class="sticky top-0 z-50 bg-white border-b border-gray-200">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ mobileSearchOpen: false }">
            <!-- Row 1: menu toggle/logo, search, cart, profile -->
            <div class="flex items-center gap-3 h-16">
                <button
                    type="button"
                    class="lg:hidden inline-flex items-center justify-center rounded-lg p-2 text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-gray-200 shrink-0"
                    aria-label="Toggle navigation"
                    x-data
                    @click="$dispatch('open-mobile-nav')"
                >
                    <svg class="size-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12h16" /><path d="M4 6h16" /><path d="M4 18h16" /></svg>
                </button>

                <a href="{{ route('home') }}" class="flex items-center gap-2 text-xl font-bold text-primary shrink-0">
                    @php $appLogo = \App\Models\Setting::get('app_logo'); @endphp
                    @if ($appLogo)
                        <img src="{{ Storage::url($appLogo) }}" alt="{{ \App\Models\Setting::get('app_name', config('app.name', 'Laravel')) }}" class="h-8 w-auto" />
                    @else
                        {{ \App\Models\Setting::get('app_name', config('app.name', 'Laravel')) }}
                    @endif
                </a>

                <form action="{{ route('products.index') }}" method="GET" class="hidden sm:block flex-1 min-w-0">
                    <label for="navbar-search" class="input w-full">
                        <svg class="size-4 opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" /></svg>
                        <input
                            type="search"
                            id="navbar-search"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search essentials, groceries and more..."
                        >
                    </label>
                </form>

                <div class="flex-1 sm:hidden"></div>

                <button
                    type="button"
                    class="sm:hidden inline-flex items-center justify-center rounded-lg p-2 text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-gray-200 shrink-0"
                    :aria-label="mobileSearchOpen ? 'Close search' : 'Open search'"
                    @click="mobileSearchOpen = !mobileSearchOpen; if (mobileSearchOpen) { $nextTick(() => $refs.mobileSearchInput.focus()) }"
                >
                    <svg class="size-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" /></svg>
                </button>

                <div class="flex items-center gap-2 shrink-0">
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
                        <a href="{{ Route::has('login') ? route('login') : '#' }}" class="hidden sm:flex items-center gap-1.5 text-sm font-medium text-gray-700 hover:text-primary">
                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" /></svg>
                            Sign Up/Sign In
                        </a>
                    @endauth

                    <div class="w-px h-6 bg-gray-200 hidden sm:block"></div>

                    @php
                        $cartItemCount = app(\App\Services\CartService::class)->totalQty(auth()->user(), session()->getId());
                    @endphp
                    <button
                        type="button"
                        x-data
                        @click="$dispatch('open-cart-drawer')"
                        class="relative inline-flex items-center gap-1.5 rounded-lg px-2 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-gray-200"
                        aria-label="Cart{{ $cartItemCount > 0 ? " ({$cartItemCount} items)" : '' }}"
                    >
                        <span class="relative">
                            <svg class="size-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1" /><circle cx="19" cy="21" r="1" /><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" /></svg>
                            @if ($cartItemCount > 0)
                                <span class="absolute -top-1 -right-1 flex items-center justify-center min-w-[1.1rem] h-[1.1rem] px-1 rounded-full bg-primary text-primary-content text-[10px] font-semibold leading-none">
                                    {{ $cartItemCount }}
                                </span>
                            @endif
                        </span>
                        <span class="hidden sm:inline">Cart</span>
                    </button>
                </div>
            </div>

            <!-- Mobile search row -->
            <div class="sm:hidden pb-3" x-show="mobileSearchOpen" x-cloak x-transition>
                <form action="{{ route('products.index') }}" method="GET">
                    <label for="navbar-search-mobile" class="input w-full">
                        <svg class="size-4 opacity-50" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8" /><path d="m21 21-4.3-4.3" /></svg>
                        <input
                            type="search"
                            id="navbar-search-mobile"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Search essentials, groceries and more..."
                            x-ref="mobileSearchInput"
                        >
                    </label>
                </form>
            </div>

            <!-- Row 2: category menu -->
            <div class="hidden lg:flex items-center gap-6 h-11 border-t border-gray-100">
                @foreach ($navCategories as $category)
                    @if ($category->children->isNotEmpty())
                        <div class="dropdown dropdown-hover">
                            <div tabindex="0" role="button" class="flex items-center gap-1 text-sm font-medium text-gray-700 hover:text-primary whitespace-nowrap">
                                {{ $category->name }}
                                <svg class="size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <ul tabindex="0" class="dropdown-content menu bg-base-100 rounded-box z-50 w-56 p-2 shadow-sm border border-base-200">
                                @foreach ($category->children as $child)
                                    <li><a href="{{ route('products.index', ['category' => $child->slug]) }}">{{ $child->name }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="text-sm font-medium text-gray-700 hover:text-primary whitespace-nowrap">
                            {{ $category->name }}
                        </a>
                    @endif
                @endforeach
            </div>
        </nav>
    </header>

    <!-- Off-canvas mobile navigation -->
    <div
        x-data="{ navOpen: false }"
        x-on:open-mobile-nav.window="navOpen = true"
        x-show="navOpen"
        x-cloak
        class="fixed inset-0 z-90 lg:hidden"
        role="dialog"
        aria-modal="true"
    >
        <div
            class="absolute inset-0 bg-black/50"
            x-show="navOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="navOpen = false"
        ></div>
        <div
            class="absolute top-0 start-0 h-full max-w-xs w-full bg-white border-e border-gray-200 overflow-y-auto"
            x-show="navOpen"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
        >
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
                <span class="text-lg font-bold text-primary">{{ \App\Models\Setting::get('app_name', config('app.name', 'Laravel')) }}</span>
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-lg p-2 text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-4 focus:ring-gray-200"
                    aria-label="Close navigation"
                    @click="navOpen = false"
                >
                    <svg class="size-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18" /><path d="m6 6 12 12" /></svg>
                </button>
            </div>
            <nav class="flex flex-col gap-1 p-4">
                <a href="{{ route('home') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">Home</a>
                <a href="{{ route('products.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">Products</a>

                <div class="my-2 border-t border-gray-100"></div>

                @foreach ($navCategories as $category)
                    @if ($category->children->isNotEmpty())
                        <div x-data="{ open: false }">
                            <button
                                type="button"
                                class="w-full flex items-center justify-between rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100"
                                @click="open = !open"
                            >
                                {{ $category->name }}
                                <svg class="size-4 transition-transform" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div x-show="open" x-cloak class="flex flex-col gap-1 pl-4">
                                @foreach ($category->children as $child)
                                    <a href="{{ route('products.index', ['category' => $child->slug]) }}" class="rounded-lg px-3 py-2 text-sm text-gray-600 hover:bg-gray-100">{{ $child->name }}</a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                            {{ $category->name }}
                        </a>
                    @endif
                @endforeach
            </nav>
        </div>
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
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            @hasSection('footer')
                @yield('footer')
            @else
                <div class="grid grid-cols-2 gap-8 gap-y-10 sm:gap-10 lg:grid-cols-5">
                    <div class="col-span-2 lg:col-span-2">
                        <a href="{{ route('home') }}" class="flex items-center gap-2 text-xl font-bold text-primary">
                            @php $appLogo = \App\Models\Setting::get('app_logo'); @endphp
                            @if ($appLogo)
                                <img src="{{ Storage::url($appLogo) }}" alt="{{ \App\Models\Setting::get('app_name', config('app.name', 'Laravel')) }}" class="h-8 w-auto" />
                            @else
                                {{ \App\Models\Setting::get('app_name', config('app.name', 'Laravel')) }}
                            @endif
                        </a>
                        <p class="mt-4 max-w-sm text-sm text-gray-500">
                            Quality products, fast delivery, and a shopping experience you can trust — everything you need, all in one place at {{ \App\Models\Setting::get('app_name', config('app.name', 'Laravel')) }}.
                        </p>
                        <div class="mt-5 flex items-center gap-3">
                            <a href="#" aria-label="Facebook" class="inline-flex items-center justify-center size-9 rounded-full border border-gray-200 text-gray-500 hover:text-primary hover:border-primary">
                                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9v-2.89h2.54V9.8c0-2.5 1.49-3.89 3.78-3.89 1.09 0 2.24.2 2.24.2v2.47h-1.26c-1.24 0-1.63.77-1.63 1.56v1.87h2.78l-.44 2.89h-2.34v6.99A10 10 0 0 0 22 12" /></svg>
                            </a>
                            <a href="#" aria-label="Instagram" class="inline-flex items-center justify-center size-9 rounded-full border border-gray-200 text-gray-500 hover:text-primary hover:border-primary">
                                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" /><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" /><path d="M17.5 6.5h.01" /></svg>
                            </a>
                            <a href="#" aria-label="X (Twitter)" class="inline-flex items-center justify-center size-9 rounded-full border border-gray-200 text-gray-500 hover:text-primary hover:border-primary">
                                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M18.9 2H22l-7.2 8.2L22.6 22h-6.6l-5.2-6.8L4.9 22H1.8l7.7-8.8L1.4 2H8l4.7 6.2L18.9 2Zm-2.3 18h1.8L7.5 4H5.6l11 16Z" /></svg>
                            </a>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Shop</h3>
                        <ul class="mt-4 space-y-3 text-sm text-gray-500">
                            <li><a href="{{ route('home') }}" class="hover:text-primary">Home</a></li>
                            <li><a href="{{ route('products.index') }}" class="hover:text-primary">All Products</a></li>
                            @foreach ($navCategories->take(4) as $category)
                                <li><a href="{{ route('products.index', ['category' => $category->slug]) }}" class="hover:text-primary">{{ $category->name }}</a></li>
                            @endforeach
                        </ul>
                    </div>

                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">My Account</h3>
                        <ul class="mt-4 space-y-3 text-sm text-gray-500">
                            @auth
                                <li><a href="{{ route('profile.edit') }}" class="hover:text-primary">Profile</a></li>
                                <li><a href="{{ route('orders.index') }}" class="hover:text-primary">My Orders</a></li>
                            @else
                                <li><a href="{{ route('login') }}" class="hover:text-primary">Sign In</a></li>
                                <li><a href="{{ route('register') }}" class="hover:text-primary">Create Account</a></li>
                            @endauth
                            <li><a href="{{ route('cart.index') }}" class="hover:text-primary">Cart</a></li>
                        </ul>
                    </div>

                    <div class="col-span-2 lg:col-span-1">
                        <h3 class="text-sm font-semibold text-gray-900">Customer Care</h3>
                        <ul class="mt-4 space-y-3 text-sm text-gray-500">
                            <li><a href="#" class="hover:text-primary">FAQ</a></li>
                            <li><a href="#" class="hover:text-primary">Shipping &amp; Returns</a></li>
                            <li><a href="#" class="hover:text-primary">Terms of Service</a></li>
                            <li><a href="#" class="hover:text-primary">Privacy Policy</a></li>
                        </ul>
                    </div>
                </div>
            @endif
        </div>

        <div class="border-t border-gray-100 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-2">
                <p class="text-sm text-gray-500">
                    &copy; {{ now()->year }} {{ \App\Models\Setting::get('app_name', config('app.name', 'Laravel')) }}. All rights reserved.
                </p>
                <div class="flex items-center gap-4 text-sm text-gray-500">
                    <a href="#" class="hover:text-primary">Terms</a>
                    <a href="#" class="hover:text-primary">Privacy</a>
                </div>
            </div>
        </div>
    </footer>

    @livewireScriptConfig
</body>
</html>
