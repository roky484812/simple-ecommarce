@extends('layouts.storefront')

@section('title', 'Products')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <x-storefront.breadcrumbs :items="[['label' => 'Products']]" />

        <livewire:storefront.product-filter />
    </div>
@endsection
