@extends('layouts.storefront')

@section('title', 'My Profile')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-2xl font-bold mb-6">{{ __('My Account') }}</h1>

        <x-ui.tabs
            :tabs="[
                'profile' => 'Profile',
                'addresses' => 'Addresses',
                'security' => 'Security',
                'orders' => 'Orders',
            ]"
            :active="request('tab', 'profile')"
        >
            @php($activeTab = request('tab', 'profile'))

            @if ($activeTab === 'profile')
                @include('storefront.profile.partials.profile-tab')
            @elseif ($activeTab === 'addresses')
                @include('storefront.profile.partials.addresses-tab')
            @elseif ($activeTab === 'security')
                @include('storefront.profile.partials.security-tab')
            @elseif ($activeTab === 'orders')
                @include('storefront.profile.partials.orders-tab')
            @endif
        </x-ui.tabs>
    </div>
@endsection
