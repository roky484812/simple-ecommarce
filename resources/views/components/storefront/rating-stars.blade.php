@props([
    'rating' => 0,
    'max' => 5,
])

{{-- Static placeholder rating display (no review system yet). --}}
<div {{ $attributes->class(['flex items-center gap-0.5']) }} aria-label="Rated {{ $rating }} out of {{ $max }}">
    @for ($i = 1; $i <= $max; $i++)
        <svg
            class="size-4 {{ $i <= $rating ? 'text-warning fill-current' : 'text-gray-300 fill-current' }}"
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 20 20"
        >
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.367 2.446a1 1 0 00-.363 1.118l1.286 3.957c.3.922-.755 1.688-1.538 1.118l-3.367-2.446a1 1 0 00-1.176 0l-3.367 2.446c-.783.57-1.838-.196-1.538-1.118l1.286-3.957a1 1 0 00-.363-1.118L2.063 9.384c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.957z" />
        </svg>
    @endfor
</div>
