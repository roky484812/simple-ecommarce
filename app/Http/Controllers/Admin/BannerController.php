<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBannerRequest;
use App\Http\Requests\UpdateBannerRequest;
use App\Models\Banner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BannerController extends Controller
{
    /**
     * Display a listing of all banners.
     */
    public function index(): View
    {
        $banners = Banner::query()->orderBy('sort_order')->get();

        return view('admin.banners.index', ['banners' => $banners]);
    }

    /**
     * Show the form for creating a new banner.
     */
    public function create(): View
    {
        return view('admin.banners.create');
    }

    /**
     * Store a newly created banner in storage.
     */
    public function store(StoreBannerRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $data['image_path'] = $request->file('image')->store('banners', 'public');
        unset($data['image']);

        Banner::create($data);

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'Banner created successfully.');
    }

    /**
     * Show the form for editing the specified banner.
     */
    public function edit(Banner $banner): View
    {
        return view('admin.banners.edit', ['banner' => $banner]);
    }

    /**
     * Update the specified banner in storage.
     */
    public function update(UpdateBannerRequest $request, Banner $banner): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            // Delete old image if it's a local file
            if (! str_starts_with($banner->image_path, 'http')) {
                Storage::disk('public')->delete($banner->image_path);
            }

            $data['image_path'] = $request->file('image')->store('banners', 'public');
        }
        unset($data['image']);

        $banner->update($data);

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'Banner updated successfully.');
    }

    /**
     * Remove the specified banner from storage.
     */
    public function destroy(Banner $banner): RedirectResponse
    {
        $banner->delete();

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'Banner deleted successfully.');
    }
}
