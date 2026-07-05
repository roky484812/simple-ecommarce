@extends('layouts.storefront')

@section('title', 'Home')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <x-ui.alert variant="info" class="mb-8">
            Module 0 placeholder — storefront layout shell, design system components.
        </x-ui.alert>

        <div class="text-center mb-12">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900">Welcome to {{ config('app.name', 'Laravel') }}</h1>
            <p class="mt-3 text-gray-600">Modern, responsive shopping experience — coming together module by module.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            <x-ui.card>
                <x-ui.badge variant="brand" class="mb-3">New</x-ui.badge>
                <h3 class="font-semibold text-gray-900 mb-1">Sample Card</h3>
                <p class="text-sm text-gray-600">Product cards will render here once Module 4 is built.</p>
            </x-ui.card>
            <x-ui.card>
                <x-ui.badge variant="green" class="mb-3">In stock</x-ui.badge>
                <h3 class="font-semibold text-gray-900 mb-1">Sample Card</h3>
                <p class="text-sm text-gray-600">Product cards will render here once Module 4 is built.</p>
            </x-ui.card>
            <x-ui.card>
                <x-ui.badge variant="red" class="mb-3">Out of stock</x-ui.badge>
                <h3 class="font-semibold text-gray-900 mb-1">Sample Card</h3>
                <p class="text-sm text-gray-600">Product cards will render here once Module 4 is built.</p>
            </x-ui.card>
        </div>

        <div class="flex flex-wrap gap-3 mb-8">
            <x-ui.button variant="primary">Primary</x-ui.button>
            <x-ui.button variant="secondary">Secondary</x-ui.button>
            <x-ui.button variant="ghost">Ghost</x-ui.button>
            <x-ui.button variant="danger">Danger</x-ui.button>
            <x-ui.button onclick="document.getElementById('demo-modal').showModal()">Open modal</x-ui.button>
        </div>

        <x-ui.card class="max-w-md">
            <form class="space-y-4">
                <x-ui.input label="Email" name="email" type="email" placeholder="you@example.com" />
                <x-ui.select label="Country" name="country" placeholder="Select a country" :options="['bd' => 'Bangladesh', 'us' => 'United States']" />
                <x-ui.textarea label="Message" name="message" placeholder="Type something..." />
                <x-ui.button type="submit" class="w-full">Submit</x-ui.button>
            </form>
        </x-ui.card>
    </div>

    <x-ui.modal id="demo-modal" title="Demo modal">
        <p class="text-sm text-gray-600">This modal is powered by Preline's overlay plugin.</p>
        <x-slot:footer>
            <x-ui.button variant="secondary" onclick="document.getElementById('demo-modal').close()">Close</x-ui.button>
            <x-ui.button variant="primary" onclick="document.getElementById('demo-modal').close()">Confirm</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>
@endsection
