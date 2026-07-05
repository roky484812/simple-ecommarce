@props(['value'])

<span {{ $attributes }}>৳{{ number_format((float) $value, 2) }}</span>
