<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    @fonts

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-base-200 antialiased">

    <header class="sticky top-0 z-50 bg-white border-b border-gray-200">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="{{ route('home') }}" class="text-xl font-bold text-primary">
                    {{ config('app.name', 'Laravel') }}
                </a>

                <div class="hidden lg:flex items-center gap-6">
                    <a href="{{ route('home') }}" class="text-sm font-medium text-gray-700 hover:text-primary">Home</a>
                    <a href="{{ Route::has('products.index') ? route('products.index') : '#' }}" class="text-sm font-medium text-gray-700 hover:text-primary">Products</a>
                </div>

                <div class="flex items-center gap-2">
                    @auth
                        <a href="{{ Route::has('profile.edit') ? route('profile.edit') : '#' }}" class="text-sm font-medium text-gray-700 hover:text-primary px-2">
                            {{ Auth::user()->name }}
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">Log in</a>
                        <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Register</a>
                    @endauth
                </div>
            </div>
        </nav>
    </header>

    <main class="min-h-[60vh] flex flex-col items-center justify-center py-12 px-4">
        <div class="card w-full max-w-md bg-base-100 shadow-xl">
            <div class="card-body">
                {{ $slot }}
            </div>
        </div>
    </main>

    <footer class="bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <p class="text-sm text-gray-500 text-center">
                &copy; {{ now()->year }} {{ config('app.name', 'Laravel') }}. All rights reserved.
            </p>
        </div>
    </footer>

</body>
</html>
