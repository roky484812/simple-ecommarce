<x-guest-layout>
    <h2 class="text-2xl font-bold mb-4">{{ __('Verify Email') }}</h2>

    <p class="text-sm text-base-content/70 mb-6">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you?') }}
    </p>

    @if (session('status') == 'verification-link-sent')
        <div role="alert" class="alert alert-success mb-4">
            <span>{{ __('A new verification link has been sent to the email address you provided during registration.') }}</span>
        </div>
    @endif

    <div class="flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary">
                {{ __('Resend Verification Email') }}
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-ghost btn-sm">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
