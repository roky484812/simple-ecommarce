<section class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold">{{ __('Addresses') }}</h2>
            <p class="mt-1 text-sm text-base-content/70">{{ __('Manage your shipping addresses.') }}</p>
        </div>

        <x-ui.button type="button" variant="primary" size="sm" onclick="address_create_modal.showModal()">
            {{ __('Add Address') }}
        </x-ui.button>
    </div>

    @if (session('success'))
        <x-ui.alert variant="success">{{ session('success') }}</x-ui.alert>
    @endif

    @if ($addresses->isEmpty())
        <p class="text-sm text-base-content/70">{{ __('You have no saved addresses yet.') }}</p>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach ($addresses as $address)
                <x-ui.card>
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="font-semibold flex items-center gap-2">
                                {{ $address->label }}
                                @if ($address->is_default)
                                    <x-ui.badge variant="brand">{{ __('Default') }}</x-ui.badge>
                                @endif
                            </p>
                            <p class="text-sm text-base-content/70 mt-1">
                                {{ $address->line1 }}@if ($address->line2), {{ $address->line2 }}@endif<br>
                                {{ $address->city }}, {{ $address->state }} {{ $address->postal_code }}<br>
                                {{ $address->country }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 mt-4">
                        <x-ui.button type="button" variant="ghost" size="sm" onclick="address_edit_modal_{{ $address->id }}.showModal()">
                            {{ __('Edit') }}
                        </x-ui.button>

                        @unless ($address->is_default)
                            <form method="post" action="{{ route('profile.addresses.set-default', $address) }}">
                                @csrf
                                @method('patch')
                                <x-ui.button type="submit" variant="ghost" size="sm">{{ __('Set Default') }}</x-ui.button>
                            </form>
                        @endunless

                        <form method="post" action="{{ route('profile.addresses.destroy', $address) }}" onsubmit="return confirm('Delete this address?')">
                            @csrf
                            @method('delete')
                            <x-ui.button type="submit" variant="danger" size="sm">{{ __('Delete') }}</x-ui.button>
                        </form>
                    </div>
                </x-ui.card>

                <x-ui.modal :id="'address_edit_modal_'.$address->id" title="Edit Address">
                    @include('storefront.profile.partials.address-form', ['address' => $address, 'action' => route('profile.addresses.update', $address), 'method' => 'patch'])
                </x-ui.modal>
            @endforeach
        </div>
    @endif

    <x-ui.modal id="address_create_modal" title="Add Address">
        @include('storefront.profile.partials.address-form', ['address' => null, 'action' => route('profile.addresses.store'), 'method' => 'post'])
    </x-ui.modal>
</section>
