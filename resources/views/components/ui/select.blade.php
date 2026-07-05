@props([
    'label' => null,
    'name' => null,
    'error' => null,
    'options' => [],
    'placeholder' => null,
])

@php
    $selectId = $attributes->get('id', $name);
    $hasError = $error ?? $errors->first($name);
@endphp

<fieldset class="fieldset">
    @if ($label)
        <legend class="fieldset-legend">{{ $label }}</legend>
    @endif

    <select
        name="{{ $name }}"
        id="{{ $selectId }}"
        {{ $attributes->except(['id'])->class([
            'select w-full',
            'select-error' => $hasError,
        ]) }}
    >
        @if ($placeholder)
            <option value="" disabled selected>{{ $placeholder }}</option>
        @endif

        {{ $slot }}

        @foreach ($options as $value => $text)
            <option value="{{ $value }}">{{ $text }}</option>
        @endforeach
    </select>

    @if ($hasError)
        <p class="label text-error">{{ $hasError }}</p>
    @endif
</fieldset>
