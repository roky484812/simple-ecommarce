<x-guest-layout>
    <h2 class="text-2xl font-bold mb-6">{{ __('Register') }}</h2>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- Name -->
        <fieldset class="fieldset">
            <legend class="fieldset-legend">{{ __('Name') }}</legend>
            <input
                id="name"
                type="text"
                name="name"
                value="{{ old('name') }}"
                class="input w-full @error('name') input-error @enderror"
                placeholder="John Doe"
                required
                autofocus
                autocomplete="name"
            />
            @error('name')
                <p class="label text-error">{{ $message }}</p>
            @enderror
        </fieldset>

        <!-- Email Address -->
        <fieldset class="fieldset">
            <legend class="fieldset-legend">{{ __('Email') }}</legend>
            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                class="input w-full @error('email') input-error @enderror"
                placeholder="you@example.com"
                required
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
                placeholder="Create a password"
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
                placeholder="Re-enter your password"
                required
                autocomplete="new-password"
            />
        </fieldset>

        <!-- Submit -->
        <button type="submit" class="btn btn-primary w-full">
            {{ __('Register') }}
        </button>
    </form>

    <div class="mt-4 text-center">
        <span class="text-sm">{{ __('Already registered?') }}</span>
        <a href="{{ route('login') }}" class="link link-hover text-sm">{{ __('Log in') }}</a>
    </div>
</x-guest-layout>
