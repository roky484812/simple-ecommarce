@props([
    'variant' => 'neutral',
])

@php
    $variants = [
        'brand' => 'badge-primary',
        'neutral' => 'badge-neutral',
        'green' => 'badge-success',
        'red' => 'badge-error',
        'blue' => 'badge-info',
        'yellow' => 'badge-warning',
    ];
@endphp

<span {{ $attributes->class(['badge', $variants[$variant] ?? $variants['neutral']]) }}>
    {{ $slot }}
</span>
