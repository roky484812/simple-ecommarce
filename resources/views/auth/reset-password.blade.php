<x-guest-layout>
    <h2 class="text-2xl font-bold mb-6">{{ __('Reset Password') }}</h2>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <fieldset class="fieldset">
            <legend class="fieldset-legend">{{ __('Email') }}</legend>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email', $request->email) }}"
                class="input w-full @error('email') input-error @enderror"
                required
                autofocus
                autocomplete="username"
            />
            @error('email')
                <p class="label text-error">{{ $message }}</p>
            @enderror
        </fieldset>

        <!-- Password -->
        <fieldset class="fieldset">
            <legend class="fieldset-legend">{{ __('Password') }}</legend>
            <input
                id="password"
                type="password"
                name="password"
                class="input w-full @error('password') input-error @enderror"
                required
                autocomplete="new-password"
            />
            @error('password')
                <p class="label text-error">{{ $message }}</p>
            @enderror
        </fieldset>

        <!-- Confirm Password -->
        <fieldset class="fieldset">
            <legend class="fieldset-legend">{{ __('Confirm Password') }}</legend>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                class="input w-full"
                required
                autocomplete="new-password"
            />
        </fieldset>

        <button type="submit" class="btn btn-primary w-full">
            {{ __('Reset Password') }}
        </button>
    </form>
</x-guest-layout>
