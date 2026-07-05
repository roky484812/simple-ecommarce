<ol class="relative border-s border-base-200 ms-3 space-y-6">
    @foreach ($timelineSteps as $step)
        <li class="ms-6">
            <span
                class="absolute flex items-center justify-center w-7 h-7 rounded-full -start-3.5 ring-4 ring-base-100 {{ $step['completed'] ? 'bg-green-500 text-white' : 'bg-base-200 text-base-content/40' }}"
            >
                @if ($step['completed'])
                    <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 6 9 17l-5-5" />
                    </svg>
                @else
                    <span class="w-2.5 h-2.5 rounded-full bg-base-content/20"></span>
                @endif
            </span>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                <h3 class="font-medium {{ $step['completed'] ? 'text-gray-900' : 'text-gray-400' }}">
                    {{ ucfirst($step['status']) }}
                </h3>

                @if ($step['happened_at'])
                    <time class="text-xs text-gray-500">{{ $step['happened_at']->format('M j, Y \a\t g:ia') }}</time>
                @endif
            </div>

            @if ($step['note'])
                <p class="mt-1 text-sm text-gray-500">{{ $step['note'] }}</p>
            @endif
        </li>
    @endforeach
</ol>
