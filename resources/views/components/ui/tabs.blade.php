@props([
    'tabs' => [],
    'active' => null,
])

@php
    $tabKeys = array_keys($tabs);
    $activeTab = in_array($active, $tabKeys, true) ? $active : ($tabKeys[0] ?? null);
@endphp

<div {{ $attributes->class(['w-full']) }}>
    <div role="tablist" class="tabs tabs-lift overflow-x-auto flex-nowrap">
        @foreach ($tabs as $key => $label)
            <a
                role="tab"
                href="{{ request()->fullUrlWithQuery(['tab' => $key]) }}"
                class="tab {{ $key === $activeTab ? 'tab-active' : '' }}"
            >
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="bg-base-100 border-base-300 rounded-b-box border-x border-b p-4 sm:p-6">
        {{ $slot }}
    </div>
</div>
