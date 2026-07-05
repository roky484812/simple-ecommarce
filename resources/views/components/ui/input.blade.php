@props([
    'label' => null,
    'name' => null,
    'error' => null,
    'type' => 'text',
    'hint' => null,
])

@php
    $inputId = $attributes->get('id', $name);
    $hasError = $error ?? $errors->first($name);
@endphp

<fieldset class="fieldset">
    @if ($label)
        <legend class="fieldset-legend">{{ $label }}</legend>
    @endif

    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $inputId }}"
        {{ $attributes->except(['id'])->class([
            'input w-full',
            'input-error' => $hasError,
        ]) }}
    />

    @if ($hasError)
        <p class="label text-error">{{ $hasError }}</p>
    @elseif ($hint)
        <p class="label">{{ $hint }}</p>
    @endif
</fieldset>
