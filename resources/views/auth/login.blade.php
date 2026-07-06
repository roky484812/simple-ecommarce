<x-guest-layout>
    <h2 class="text-2xl font-bold mb-6">{{ __('Log in') }}</h2>

    <!-- Session Status -->
    @if (session('status'))
        <div role="alert" class="alert alert-success mb-4">
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

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
                placeholder="Enter your password"
                required
                autocomplete="current-password"
            />
            @error('password')
                <p class="label text-error">{{ $message }}</p>
            @enderror
        </fieldset>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="remember" class="checkbox checkbox-sm" />
                <span class="text-sm">{{ __('Remember me') }}</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="link link-hover text-sm">
                    {{ __('Forgot password?') }}
                </a>
            @endif
        </div>

        <!-- Submit -->
        <button type="submit" class="btn btn-primary w-full">
            {{ __('Log in') }}
        </button>
    </form>

    <div class="mt-4 text-center">
        <span class="text-sm">{{ __("Don't have an account?") }}</span>
        <a href="{{ route('register') }}" class="link link-hover text-sm">{{ __('Register') }}</a>
    </div>
</x-guest-layout>
