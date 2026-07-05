@props([
    'label' => null,
    'name' => null,
    'error' => null,
    'rows' => 4,
])

@php
    $textareaId = $attributes->get('id', $name);
    $hasError = $error ?? $errors->first($name);
@endphp

<fieldset class="fieldset">
    @if ($label)
        <legend class="fieldset-legend">{{ $label }}</legend>
    @endif

    <textarea
        name="{{ $name }}"
        id="{{ $textareaId }}"
        rows="{{ $rows }}"
        {{ $attributes->except(['id'])->class([
            'textarea w-full',
            'textarea-error' => $hasError,
        ]) }}
    >{{ $slot }}</textarea>

    @if ($hasError)
        <p class="label text-error">{{ $hasError }}</p>
    @endif
</fieldset>
