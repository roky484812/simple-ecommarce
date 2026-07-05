<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Show the settings form.
     */
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'appName' => Setting::get('app_name', config('app.name', 'Laravel')),
            'logoPath' => Setting::get('app_logo'),
        ]);
    }

    /**
     * Update the settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'app_name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'file', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
        ]);

        Setting::set('app_name', $request->input('app_name'));

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            $oldLogo = Setting::get('app_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }

            $path = $request->file('logo')->store('settings', 'public');
            Setting::set('app_logo', $path);
        }

        if ($request->boolean('remove_logo') && ! $request->hasFile('logo')) {
            $oldLogo = Setting::get('app_logo');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            Setting::set('app_logo', null);
        }

        return redirect()
            ->route('admin.settings.edit')
            ->with('success', 'Settings updated successfully.');
    }
}
