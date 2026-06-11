<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppBanner;
use Illuminate\Http\Request;

class AppBannerController extends Controller
{
    public function index(Request $request)
    {
        $query = AppBanner::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderByDesc('id');

        if ($request->filled('position')) {
            $query->where('position', $request->position);
        }

        $banners = $query->get();

        if ($banners->isEmpty()) {
            $banners = collect([$this->dummyBanner($request->position ?: 'home_top')]);
        }

        return response()->json([
            'success' => true,
            'data' => $banners,
        ]);
    }

    private function dummyBanner(string $position): array
    {
        return [
            'id' => 'dummy-'.$position,
            'title' => $position === 'services_top'
                ? 'Detailing that shines'
                : 'Book doorstep car wash in minutes',
            'subtitle' => $position === 'services_top'
                ? 'Explore premium care plans for every vehicle.'
                : 'Professional care for your car, at your doorstep.',
            'image' => null,
            'image_url' => null,
            'position' => $position,
            'type' => 'screen',
            'redirect_screen' => '/services',
            'redirect_value' => null,
            'sort_order' => 0,
            'is_active' => true,
            'background_color' => '#82D9F0',
            'button_label' => 'Book Now',
        ];
    }
}
