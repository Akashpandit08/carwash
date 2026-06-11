<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppBanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AppBannerController extends Controller
{
    public function index()
    {
        $banners = AppBanner::orderBy('position')->orderBy('sort_order')->paginate(20);

        return view('admin.app-banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.app-banners.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateBanner($request);

        if ($request->hasFile('image')) {
            $result = $request->file('image')->storeOnCloudinary('app_banners');
            $validated['image'] = $result->getSecurePath();
        }

        $validated['is_active'] = $request->boolean('is_active');

        AppBanner::create($validated);

        return redirect()->route('admin.app-banners.index')->with('success', 'App banner created successfully.');
    }

    public function edit(AppBanner $appBanner)
    {
        return view('admin.app-banners.edit', compact('appBanner'));
    }

    public function update(Request $request, AppBanner $appBanner)
    {
        $validated = $this->validateBanner($request, false);

        if ($request->hasFile('image')) {
            if ($appBanner->image) {
                // To delete from Cloudinary, we would need the public ID. 
                // For simplicity, we just upload the new one.
            }
            $result = $request->file('image')->storeOnCloudinary('app_banners');
            $validated['image'] = $result->getSecurePath();
        }

        $validated['is_active'] = $request->boolean('is_active');

        $appBanner->update($validated);

        return redirect()->route('admin.app-banners.index')->with('success', 'App banner updated successfully.');
    }

    public function destroy(AppBanner $appBanner)
    {
        if ($appBanner->image) {
            // Can be deleted via Cloudinary API using public ID if extracted
        }

        $appBanner->delete();

        return redirect()->route('admin.app-banners.index')->with('success', 'App banner deleted successfully.');
    }

    private function validateBanner(Request $request, bool $imageRequired = true): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'image' => [$imageRequired ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'position' => 'required|string|max:80',
            'type' => 'required|in:screen,service,booking,external,none',
            'redirect_screen' => 'nullable|string|max:120',
            'redirect_value' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
    }
}
