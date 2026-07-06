@extends('layouts.storefront')

@section('content')
    <div class="min-h-[60vh] flex flex-col items-center justify-center py-12 px-4">
        <div class="card w-full max-w-md bg-base-100 shadow-xl">
            <div class="card-body">
                {{ $slot }}
            </div>
        </div>
    </div>
@endsection
