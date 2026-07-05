<section>
    <h2 class="text-lg font-semibold">{{ __('Delete Account') }}</h2>
    <p class="mt-1 text-sm text-base-content/70">
        {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
    </p>

    <div class="mt-4">
        <button type="button" class="btn btn-error" onclick="document.getElementById('confirm-user-deletion').showModal()">
            {{ __('Delete Account') }}
        </button>
    </div>

    <dialog id="confirm-user-deletion" class="modal">
        <div class="modal-box">
            <form method="post" action="{{ route('profile.destroy') }}">
                @csrf
                @method('delete')

                <h3 class="text-lg font-bold">{{ __('Are you sure you want to delete your account?') }}</h3>

                <p class="mt-2 text-sm text-base-content/70">
                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                </p>

                <fieldset class="fieldset mt-4">
                    <legend class="fieldset-legend">{{ __('Password') }}</legend>
                    <input
                        name="password"
                        type="password"
                        class="input w-full"
                        placeholder="{{ __('Password') }}"
                    />
                    @if ($errors->userDeletion->has('password'))
                        <p class="label text-error">{{ $errors->userDeletion->first('password') }}</p>
                    @endif
                </fieldset>

                <div class="modal-action">
                    <button type="button" class="btn" onclick="document.getElementById('confirm-user-deletion').close()">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-error">
                        {{ __('Delete Account') }}
                    </button>
                </div>
            </form>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
</section>
