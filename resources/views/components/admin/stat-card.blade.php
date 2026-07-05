@props([
    'label',
    'value',
    'variant' => 'neutral',
])

@php
    $variants = [
        'neutral' => 'text-gray-900',
        'brand' => 'text-primary',
        'green' => 'text-success',
        'red' => 'text-error',
        'yellow' => 'text-warning',
    ];
@endphp

<x-ui.card>
    <p class="text-sm text-gray-500">{{ $label }}</p>
    <p class="text-3xl font-bold mt-1 {{ $variants[$variant] ?? $variants['neutral'] }}">{{ $value }}</p>

    @isset($slot)
        <div class="mt-2 text-xs text-gray-500">{{ $slot }}</div>
    @endisset
</x-ui.card>
