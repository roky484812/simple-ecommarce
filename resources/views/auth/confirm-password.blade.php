<x-guest-layout>
    <h2 class="text-2xl font-bold mb-4">{{ __('Confirm Password') }}</h2>

    <p class="text-sm text-base-content/70 mb-6">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </p>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <!-- Password -->
        <fieldset class="fieldset">
            <legend class="fieldset-legend">{{ __('Password') }}</legend>
            <input
                id="password"
                type="password"
                name="password"
                class="input w-full @error('password') input-error @enderror"
                placeholder="Enter your password"
                required
                autocomplete="current-password"
            />
            @error('password')
                <p class="label text-error">{{ $message }}</p>
            @enderror
        </fieldset>

        <button type="submit" class="btn btn-primary w-full">
            {{ __('Confirm') }}
        </button>
    </form>
</x-guest-layout>
