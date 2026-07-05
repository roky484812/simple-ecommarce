<x-guest-layout>
    <h2 class="text-2xl font-bold mb-4">{{ __('Forgot Password') }}</h2>

    <p class="text-sm text-base-content/70 mb-6">
        {{ __('No problem. Just enter your email address and we will send you a password reset link.') }}
    </p>

    <!-- Session Status -->
    @if (session('status'))
        <div role="alert" class="alert alert-success mb-4">
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
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
                required
                autofocus
            />
            @error('email')
                <p class="label text-error">{{ $message }}</p>
            @enderror
        </fieldset>

        <button type="submit" class="btn btn-primary w-full">
            {{ __('Email Password Reset Link') }}
        </button>
    </form>

    <div class="mt-4 text-center">
        <a href="{{ route('login') }}" class="link link-hover text-sm">{{ __('Back to login') }}</a>
    </div>
</x-guest-layout>
