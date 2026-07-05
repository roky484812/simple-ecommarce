@props(['items' => []])

{{--
    $items: array<int, array{label: string, url?: string}> — last item is
    treated as the current page (no link) if 'url' is omitted.
--}}
<nav aria-label="Breadcrumb" {{ $attributes->class(['text-sm mb-6']) }}>
    <ol class="flex items-center gap-1.5 flex-wrap text-gray-500">
        <li>
            <a href="{{ route('home') }}" class="hover:text-brand-700">Home</a>
        </li>

        @foreach ($items as $item)
            <li class="flex items-center gap-1.5">
                <svg class="size-3.5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6" /></svg>

                @if (! empty($item['url']))
                    <a href="{{ $item['url'] }}" class="hover:text-brand-700">{{ $item['label'] }}</a>
                @else
                    <span class="text-gray-900 font-medium" aria-current="page">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
