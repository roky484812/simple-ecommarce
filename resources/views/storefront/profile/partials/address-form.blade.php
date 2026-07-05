<form method="post" action="{{ $action }}" class="space-y-4">
    @csrf
    @if ($method === 'patch')
        @method('patch')
    @endif

    <x-ui.input label="Label" name="label" placeholder="Home, Office..." :value="old('label', $address?->label)" required />

    <x-ui.input label="Address Line 1" name="line1" :value="old('line1', $address?->line1)" required />

    <x-ui.input label="Address Line 2" name="line2" :value="old('line2', $address?->line2)" />

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-ui.input label="City" name="city" :value="old('city', $address?->city)" required />
        <x-ui.input label="State" name="state" :value="old('state', $address?->state)" required />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-ui.input label="Postal Code" name="postal_code" :value="old('postal_code', $address?->postal_code)" required />
        <x-ui.input label="Country" name="country" :value="old('country', $address?->country)" required />
    </div>

    <label class="label cursor-pointer justify-start gap-2">
        <input type="checkbox" name="is_default" value="1" class="checkbox" @checked(old('is_default', $address?->is_default))>
        {{ __('Set as default address') }}
    </label>

    <div class="modal-action">
        <x-ui.button type="submit" variant="primary">{{ __('Save') }}</x-ui.button>
    </div>
</form>
