@if (session('status') === 'password-updated')
    <x-ui.alert variant="success">{{ __('Password updated successfully.') }}</x-ui.alert>
@endif

@include('profile.partials.update-password-form')

<div class="divider"></div>

@include('profile.partials.delete-user-form')
