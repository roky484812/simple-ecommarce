@props([
    'label' => null,
    'name' => null,
    'error' => null,
    'options' => [],
    'placeholder' => null,
    'value' => null,
])

@php
    $selectId = $attributes->get('id', $name);
    $hasError = $error ?? $errors->first($name);
    $currentValue = old($name, $value);
@endphp

<fieldset class="fieldset">
    @if ($label)
        <legend class="fieldset-legend">{{ $label }}</legend>
    @endif

    <select
        name="{{ $name }}"
        id="{{ $selectId }}"
        {{ $attributes->except(['id', 'value'])->class([
            'select w-full',
            'select-error' => $hasError,
        ]) }}
    >
        @if ($placeholder)
            <option value="" @selected(blank($currentValue))>{{ $placeholder }}</option>
        @endif

        {{ $slot }}

        @foreach ($options as $optionValue => $text)
            <option value="{{ $optionValue }}" @selected((string) $currentValue === (string) $optionValue)>{{ $text }}</option>
        @endforeach
    </select>

    @if ($hasError)
        <p class="label text-error">{{ $hasError }}</p>
    @endif
</fieldset>
