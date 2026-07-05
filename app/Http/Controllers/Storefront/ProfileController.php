<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile page (profile info, addresses, security, orders tabs).
     */
    public function edit(Request $request): View
    {
        $user = $request->user()->load('profile', 'addresses');

        return view('storefront.profile.edit', [
            'user' => $user,
            'addresses' => $user->addresses,
            'recentOrders' => $user->orders()->withCount('items')->latest()->limit(5)->get(),
        ]);
    }

    /**
     * Update the user's profile information (name/email/phone/avatar/bio/dob/gender).
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $profile = $user->profile()->first() ?? $user->profile()->make();

        if ($request->hasFile('avatar')) {
            if ($profile->avatar_path) {
                Storage::disk('public')->delete($profile->avatar_path);
            }

            $profile->avatar_path = $request->file('avatar')->store('avatars', 'public');
        }

        $profile->bio = $data['bio'] ?? null;
        $profile->date_of_birth = $data['date_of_birth'] ?? null;
        $profile->gender = $data['gender'] ?? null;
        $profile->user()->associate($user);
        $profile->save();

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        auth()->logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to('/');
    }

    /**
     * Store a new address for the authenticated user.
     */
    public function storeAddress(StoreAddressRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $data['is_default'] = $request->boolean('is_default');

        if ($data['is_default'] || $user->addresses()->doesntExist()) {
            $data['is_default'] = true;
            $user->addresses()->update(['is_default' => false]);
        }

        $user->addresses()->create($data);

        return redirect()->route('profile.edit', ['tab' => 'addresses'])->with('success', 'Address added successfully.');
    }

    /**
     * Update an existing address for the authenticated user.
     */
    public function updateAddress(UpdateAddressRequest $request, Address $address): RedirectResponse
    {
        $data = $request->validated();
        $data['is_default'] = $request->boolean('is_default');

        if ($data['is_default']) {
            $address->user->addresses()->whereKeyNot($address->id)->update(['is_default' => false]);
        }

        $address->update($data);

        return redirect()->route('profile.edit', ['tab' => 'addresses'])->with('success', 'Address updated successfully.');
    }

    /**
     * Delete an address belonging to the authenticated user.
     */
    public function destroyAddress(Request $request, Address $address): RedirectResponse
    {
        abort_unless($request->user()->is($address->user), 403);

        $wasDefault = $address->is_default;

        $address->delete();

        if ($wasDefault) {
            $request->user()->addresses()->first()?->update(['is_default' => true]);
        }

        return redirect()->route('profile.edit', ['tab' => 'addresses'])->with('success', 'Address deleted successfully.');
    }

    /**
     * Mark an address as the user's default.
     */
    public function setDefaultAddress(Request $request, Address $address): RedirectResponse
    {
        abort_unless($request->user()->is($address->user), 403);

        $address->user->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return redirect()->route('profile.edit', ['tab' => 'addresses'])->with('success', 'Default address updated.');
    }
}
