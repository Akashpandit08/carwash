<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppBanner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index()
    {
        $banners = AppBanner::orderBy('sort_order')->get();
        return response()->json(['success' => true, 'data' => $banners]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'image_url' => 'required|string|max:255',
            'target_url' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $banner = AppBanner::create($validated);
        return response()->json(['success' => true, 'message' => 'Banner created.', 'data' => $banner], 201);
    }

    public function show(AppBanner $banner)
    {
        return response()->json(['success' => true, 'data' => $banner]);
    }

    public function update(Request $request, AppBanner $banner)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'image_url' => 'required|string|max:255',
            'target_url' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $banner->update($validated);
        return response()->json(['success' => true, 'message' => 'Banner updated.', 'data' => $banner]);
    }

    public function destroy(AppBanner $banner)
    {
        $banner->delete();
        return response()->json(['success' => true, 'message' => 'Banner deleted.']);
    }
}
