@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-sm text-gray-600">Module 0 placeholder — admin layout shell.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-ui.card>
            <p class="text-sm text-gray-500">Total Sales</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">$0.00</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-sm text-gray-500">Orders</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">0</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-sm text-gray-500">Products</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">0</p>
        </x-ui.card>
        <x-ui.card>
            <p class="text-sm text-gray-500">Customers</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">0</p>
        </x-ui.card>
    </div>
@endsection
