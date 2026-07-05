@props([
    'id',
    'title' => null,
])

{{--
    Native <dialog>-based modal (daisyUI's recommended approach).
    Open with: document.getElementById('{{ $id }}').showModal()
    Close with: document.getElementById('{{ $id }}').close()
    Or trigger via a button that has: onclick="{{ $id }}.showModal()"
--}}
<dialog id="{{ $id }}" class="modal">
    <div class="modal-box">
        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" aria-label="Close">✕</button>
        </form>

        @if ($title)
            <h3 class="text-lg font-bold">{{ $title }}</h3>
        @endif

        <div class="py-2">
            {{ $slot }}
        </div>

        @isset($footer)
            <div class="modal-action">
                {{ $footer }}
            </div>
        @endisset
    </div>

    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>
