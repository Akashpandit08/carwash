<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        $query = Banner::query();

        if ($request->filled('search')) {
            $query->where('title', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $banners = $query->orderBy('sort_order')->latest()->paginate(20)->withQueryString();

        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.create', ['banner' => new Banner()]);
    }

    public function store(Request $request)
    {
        $data = $this->validateBanner($request);
        $data['image'] = $request->file('image')->storeOnCloudinary('banners')->getSecurePath();
        $data['is_active'] = $request->boolean('is_active');

        Banner::create($data);

        return redirect()->route('admin.banners.index')->with('success', 'Banner created successfully.');
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $data = $this->validateBanner($request, false);

        if ($request->hasFile('image')) {
            // Storage::disk('public')->delete($banner->image);
            $data['image'] = $request->file('image')->storeOnCloudinary('banners')->getSecurePath();
        }

        $data['is_active'] = $request->boolean('is_active');
        $banner->update($data);

        return redirect()->route('admin.banners.index')->with('success', 'Banner updated successfully.');
    }

    public function destroy(Banner $banner)
    {
        // Storage::disk('public')->delete($banner->image);
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('success', 'Banner deleted successfully.');
    }

    public function toggle(Banner $banner)
    {
        $banner->update(['is_active' => ! $banner->is_active]);

        return back()->with('success', 'Banner status updated.');
    }

    private function validateBanner(Request $request, bool $imageRequired = true): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'image' => [$imageRequired ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'redirect_type' => ['required', Rule::in(['home', 'services', 'service_detail', 'booking', 'booking_detail', 'offers', 'profile', 'external_url', 'custom_screen'])],
            'redirect_value' => ['nullable', 'string', 'max:255'],
            'user_type' => ['required', Rule::in(['all', 'customer', 'partner', 'driver', 'worker'])],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
