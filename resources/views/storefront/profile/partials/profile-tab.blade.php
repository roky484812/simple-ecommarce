@php($profile = $user->profile)

<section class="space-y-6">
    <div>
        <h2 class="text-lg font-semibold">{{ __('Profile Information') }}</h2>
        <p class="mt-1 text-sm text-base-content/70">
            {{ __("Update your account's profile information, avatar and bio.") }}
        </p>
    </div>

    @if (session('success'))
        <x-ui.alert variant="success">{{ session('success') }}</x-ui.alert>
    @endif

    <form method="post" action="{{ route('profile.update') }}" class="space-y-4" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div class="flex items-center gap-4">
            <div
                x-data="{ previewUrl: null, filePicked(event) { const file = event.target.files[0]; if (file) { this.previewUrl = URL.createObjectURL(file); } } }"
                class="group relative avatar cursor-pointer"
                onclick="document.getElementById('avatar-input').click()"
            >
                <div class="w-28 rounded-full">
                    <img
                        x-show="!previewUrl"
                        x-cloak
                        src="{{ $user->avatarUrl() }}"
                        alt="{{ $user->name }}"
                    />
                    <img x-show="previewUrl" x-cloak :src="previewUrl" alt="{{ $user->name }}" />
                </div>

                <div class="absolute inset-0 rounded-full flex items-center justify-center bg-black/0 group-hover:bg-black/40 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-9 h-9 text-white opacity-0 group-hover:opacity-100 transition-opacity">
                        <path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z" />
                        <circle cx="12" cy="13" r="3" />
                    </svg>
                </div>

                <input
                    id="avatar-input"
                    type="file"
                    name="avatar"
                    accept="image/*"
                    class="hidden"
                    @change="filePicked($event)"
                />
            </div>

            <div class="flex-1">
                <p class="text-sm font-medium">{{ __('Profile photo') }}</p>
                <p class="text-sm text-base-content/70">{{ __('Hover the avatar and click to upload a new photo.') }}</p>
                @error('avatar')
                    <p class="label text-error">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <x-ui.input label="Name" name="name" :value="old('name', $user->name)" required autofocus autocomplete="name" />

        <x-ui.input label="Email" type="email" name="email" :value="old('email', $user->email)" required autocomplete="username" />

        <x-ui.input label="Phone" name="phone" :value="old('phone', $user->phone)" autocomplete="tel" />

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-ui.input label="Date of Birth" type="date" name="date_of_birth" :value="old('date_of_birth', $profile?->date_of_birth?->format('Y-m-d'))" />

            <x-ui.select label="Gender" name="gender" :value="old('gender', $profile?->gender)" placeholder="Select gender">
                <option value="male" @selected(old('gender', $profile?->gender) === 'male')>Male</option>
                <option value="female" @selected(old('gender', $profile?->gender) === 'female')>Female</option>
                <option value="other" @selected(old('gender', $profile?->gender) === 'other')>Other</option>
            </x-ui.select>
        </div>

        <x-ui.textarea label="Bio" name="bio" rows="4">{{ old('bio', $profile?->bio) }}</x-ui.textarea>

        <div class="flex items-center gap-4">
            <x-ui.button type="submit" variant="primary">{{ __('Save') }}</x-ui.button>
        </div>
    </form>
</section>
