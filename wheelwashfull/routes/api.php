<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes (no authentication required)
Route::prefix('auth')->middleware('throttle:6,1')->group(function () {
    Route::post('/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
});

Route::post('/payments/razorpay/webhook', [\App\Http\Controllers\Api\PaymentController::class, 'webhook']);
Route::get('/app-banners', [\App\Http\Controllers\Api\AppBannerController::class, 'index']);

// Protected routes (authentication required)
Route::middleware('auth:api')->group(function () {
    // Common routes for all authenticated users
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::prefix('app')->group(function () {
        Route::get('/banners', [\App\Http\Controllers\Api\AppContentController::class, 'banners']);
        Route::get('/home', [\App\Http\Controllers\Api\AppContentController::class, 'home']);
        Route::post('/device-token', [\App\Http\Controllers\Api\DeviceController::class, 'store']);
        Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
        Route::post('/notifications/{id}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markRead']);
        Route::post('/notifications/read-all', [\App\Http\Controllers\Api\NotificationController::class, 'readAll']);
    });

    Route::prefix('partner')->group(function () {
        Route::get('/banners', [\App\Http\Controllers\Api\AppContentController::class, 'banners']);
        Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
        Route::post('/device-token', [\App\Http\Controllers\Api\DeviceController::class, 'store']);
    });

    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\Admin\DashboardController::class, 'index']);
        Route::get('/dashboard', [\App\Http\Controllers\Api\Admin\DashboardController::class, 'index']);
        Route::get('/reports', [\App\Http\Controllers\Api\Admin\ReportController::class, 'index']);
        Route::get('/bookings', [\App\Http\Controllers\Api\Admin\BookingController::class, 'index']);
        Route::get('/bookings/{booking}', [\App\Http\Controllers\Api\Admin\BookingController::class, 'show']);
        Route::post('/bookings/{booking}/assign-worker', [\App\Http\Controllers\Api\Admin\BookingController::class, 'assignWorker']);
        Route::post('/bookings/{booking}/assign-partner', [\App\Http\Controllers\Api\Admin\BookingController::class, 'assignPartner']);
        Route::post('/bookings/{booking}/assign-pickup-driver', [\App\Http\Controllers\Api\Admin\BookingController::class, 'assignPickupDriver']);
        Route::post('/bookings/{booking}/status', [\App\Http\Controllers\Api\Admin\BookingController::class, 'updateStatus']);
        Route::get('/bookings/{booking}/status-logs', [\App\Http\Controllers\Api\Admin\BookingController::class, 'statusLogs']);
        Route::get('/bookings/{booking}/media', [\App\Http\Controllers\Api\Admin\BookingController::class, 'media']);
        Route::get('/bookings/{booking}/tracking', [\App\Http\Controllers\Api\Admin\BookingController::class, 'tracking']);

        Route::get('/workers', [\App\Http\Controllers\Api\Admin\WorkerController::class, 'index']);
        Route::post('/workers', [\App\Http\Controllers\Api\Admin\WorkerController::class, 'store']);
        Route::put('/workers/{worker}', [\App\Http\Controllers\Api\Admin\WorkerController::class, 'update']);

        Route::get('/pickup-drivers', [\App\Http\Controllers\Api\Admin\PickupDriverController::class, 'index']);
        Route::post('/pickup-drivers', [\App\Http\Controllers\Api\Admin\PickupDriverController::class, 'store']);
        Route::put('/pickup-drivers/{driver}', [\App\Http\Controllers\Api\Admin\PickupDriverController::class, 'update']);

        Route::get('/partners', [\App\Http\Controllers\Api\Admin\PartnerController::class, 'index']);
        Route::post('/partners', [\App\Http\Controllers\Api\Admin\PartnerController::class, 'store']);
        Route::put('/partners/{partner}', [\App\Http\Controllers\Api\Admin\PartnerController::class, 'update']);

        Route::get('/payouts', [\App\Http\Controllers\Api\Admin\PayoutController::class, 'index']);
        Route::post('/payouts/{payout}/approve', [\App\Http\Controllers\Api\Admin\PayoutController::class, 'approve']);
        Route::post('/payouts/{payout}/mark-paid', [\App\Http\Controllers\Api\Admin\PayoutController::class, 'markPaid']);

        Route::get('/omneaxa-whatsapp/status', [\App\Http\Controllers\Admin\OmneaxaController::class, 'status']);
    });

    Route::middleware('role:worker,partner,pickup_driver')->prefix('operations')->group(function () {
        Route::post('/location/update', [\App\Http\Controllers\Api\Operations\LocationController::class, 'update']);

        Route::middleware('role:worker')->prefix('worker')->group(function () {
            Route::get('/dashboard', [\App\Http\Controllers\Api\Operations\Worker\JobController::class, 'dashboard']);
            Route::get('/earnings', [\App\Http\Controllers\Api\Operations\Worker\JobController::class, 'earnings']);
            Route::get('/jobs', [\App\Http\Controllers\Api\Operations\Worker\JobController::class, 'index']);
            Route::get('/jobs/{booking}', [\App\Http\Controllers\Api\Operations\Worker\JobController::class, 'show']);
            Route::post('/jobs/{booking}/status', [\App\Http\Controllers\Api\Operations\Worker\JobController::class, 'status']);
            Route::post('/jobs/{booking}/media', [\App\Http\Controllers\Api\Operations\Worker\JobController::class, 'media']);
        });

        Route::middleware('role:partner')->prefix('partner')->group(function () {
            Route::get('/dashboard', [\App\Http\Controllers\Api\Operations\Partner\JobController::class, 'dashboard']);
            Route::get('/workers', [\App\Http\Controllers\Api\Operations\Partner\WorkerController::class, 'index']);
            Route::get('/jobs', [\App\Http\Controllers\Api\Operations\Partner\JobController::class, 'index']);
            Route::get('/jobs/{booking}', [\App\Http\Controllers\Api\Operations\Partner\JobController::class, 'show']);
            Route::post('/jobs/{booking}/assign-worker', [\App\Http\Controllers\Api\Operations\Partner\JobController::class, 'assignWorker']);
            Route::post('/jobs/{booking}/status', [\App\Http\Controllers\Api\Operations\Partner\JobController::class, 'status']);
            Route::post('/jobs/{booking}/media', [\App\Http\Controllers\Api\Operations\Partner\JobController::class, 'media']);
        });

        Route::middleware('role:pickup_driver')->prefix('driver')->group(function () {
            Route::get('/dashboard', [\App\Http\Controllers\Api\Operations\Driver\JobController::class, 'dashboard']);
            Route::get('/earnings', [\App\Http\Controllers\Api\Operations\Driver\JobController::class, 'earnings']);
            Route::get('/jobs', [\App\Http\Controllers\Api\Operations\Driver\JobController::class, 'index']);
            Route::get('/jobs/{booking}', [\App\Http\Controllers\Api\Operations\Driver\JobController::class, 'show']);
            Route::post('/jobs/{booking}/status', [\App\Http\Controllers\Api\Operations\Driver\JobController::class, 'status']);
            Route::post('/jobs/{booking}/media', [\App\Http\Controllers\Api\Operations\Driver\JobController::class, 'media']);
        });
    });

    // Customer routes
    Route::middleware('role:customer')->prefix('customer')->group(function () {
        // Add customer-specific routes here
        Route::get('/dashboard', function () {
            return response()->json([
                'success' => true,
                'message' => 'Welcome to customer dashboard',
            ]);
        });

        // Vehicles
        Route::apiResource('vehicles', \App\Http\Controllers\Api\VehicleController::class);

        // Profile
        Route::get('/profile', [\App\Http\Controllers\Api\ProfileController::class, 'show']);
        Route::put('/profile', [\App\Http\Controllers\Api\ProfileController::class, 'update']);

        // Home Feed
        Route::get('/home', [\App\Http\Controllers\Api\Customer\HomeController::class, 'index']);
        Route::get('/banners', [\App\Http\Controllers\Api\AppBannerController::class, 'index']);

        // Addresses
        Route::apiResource('addresses', \App\Http\Controllers\Api\Customer\AddressController::class);

        // Services
        Route::get('/services', [\App\Http\Controllers\Api\ServiceController::class, 'index']);
        Route::get('/services/{service}', [\App\Http\Controllers\Api\ServiceController::class, 'show']);

        // Bookings
        Route::get('/available-slots', [\App\Http\Controllers\Api\BookingController::class, 'availableSlots']);

        Route::prefix('bookings')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\BookingController::class, 'index']);
            Route::get('/slots', [\App\Http\Controllers\Api\BookingController::class, 'slots']);
            Route::post('/apply-coupon', [\App\Http\Controllers\Api\BookingController::class, 'applyCoupon']);
            Route::post('/', [\App\Http\Controllers\Api\BookingController::class, 'store']);
            Route::get('/{booking}', [\App\Http\Controllers\Api\BookingController::class, 'show']);
            Route::get('/{booking}/track', [\App\Http\Controllers\Api\BookingController::class, 'track']);
            Route::get('/{booking}/tracking', [\App\Http\Controllers\Api\BookingController::class, 'track']);
            Route::post('/{booking}/review', [\App\Http\Controllers\Api\BookingController::class, 'review']);
            Route::post('/{booking}/cancel', [\App\Http\Controllers\Api\BookingController::class, 'cancel']);
        });

        // Payments
        Route::prefix('payments')->group(function () {
            Route::get('/{payment}/checkout', [\App\Http\Controllers\Api\PaymentController::class, 'checkout']);
            Route::post('/{payment}/success', [\App\Http\Controllers\Api\PaymentController::class, 'success']);
            Route::post('/{payment}/failed', [\App\Http\Controllers\Api\PaymentController::class, 'failed']);
            Route::post('/{payment}/verify', [\App\Http\Controllers\Api\PaymentController::class, 'verify']);
        });

        // Slots
        Route::get('/slots', [\App\Http\Controllers\Api\SlotController::class, 'index']);

        // Coupons
        Route::prefix('coupons')->group(function () {
            Route::get('/', [\App\Http\Controllers\Customer\CouponController::class, 'index']);
            Route::post('/apply', [\App\Http\Controllers\Customer\CouponController::class, 'apply']);
        });

        // Ratings & Reviews
        Route::prefix('ratings')->group(function () {
            Route::get('/', [\App\Http\Controllers\Customer\RatingController::class, 'index']);
            Route::post('/', [\App\Http\Controllers\Customer\RatingController::class, 'store']);
            Route::get('/booking', [\App\Http\Controllers\Customer\RatingController::class, 'getByBooking']);
            Route::get('/{rating}', [\App\Http\Controllers\Customer\RatingController::class, 'show']);
            Route::put('/{rating}', [\App\Http\Controllers\Customer\RatingController::class, 'update']);
            Route::delete('/{rating}', [\App\Http\Controllers\Customer\RatingController::class, 'destroy']);
        });

        // Help & Support
        Route::prefix('support')->group(function () {
            Route::get('/faqs', [\App\Http\Controllers\Api\Customer\SupportController::class, 'faqs']);
            Route::post('/ticket', [\App\Http\Controllers\Api\Customer\SupportController::class, 'submitTicket']);
        });
    });

    // Partner routes
    Route::middleware('role:partner')->prefix('partner')->group(function () {
        // Add partner-specific routes here
        Route::get('/dashboard', function () {
            return response()->json([
                'success' => true,
                'message' => 'Welcome to partner dashboard',
            ]);
        });

        // Bookings Management
        Route::get('/earnings', [\App\Http\Controllers\Api\Partner\BookingController::class, 'earnings']);

        Route::prefix('bookings')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Partner\BookingController::class, 'index']);
            Route::get('/{booking}', [\App\Http\Controllers\Api\Partner\BookingController::class, 'show']);
            Route::post('/{booking}/images', [\App\Http\Controllers\Api\Partner\BookingController::class, 'uploadImage']);
            Route::post('/{booking}/status', [\App\Http\Controllers\Api\Partner\BookingController::class, 'updateStatus']);
            Route::post('/{booking}/collect-cod', [\App\Http\Controllers\Api\Partner\BookingController::class, 'collectCod']);
        });

        // Ratings & Reviews
        Route::prefix('ratings')->group(function () {
            Route::get('/', [\App\Http\Controllers\Partner\RatingController::class, 'index']);
            Route::get('/statistics', [\App\Http\Controllers\Partner\RatingController::class, 'statistics']);
            Route::get('/recent', [\App\Http\Controllers\Partner\RatingController::class, 'recent']);
        });
    });

    // Admin API endpoints (Continued from above)
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        // Services, Slots, Banners, Customers
        Route::apiResource('services', \App\Http\Controllers\Api\Admin\ServiceController::class);
        Route::apiResource('slots', \App\Http\Controllers\Api\Admin\SlotController::class);
        Route::apiResource('banners', \App\Http\Controllers\Api\Admin\BannerController::class);
        Route::apiResource('customers', \App\Http\Controllers\Api\Admin\CustomerController::class)->only(['index', 'show']);

        // Coupon Management
        Route::prefix('coupons')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\CouponController::class, 'index']);
            Route::post('/', [\App\Http\Controllers\Admin\CouponController::class, 'store']);
            Route::get('/{coupon}', [\App\Http\Controllers\Admin\CouponController::class, 'show']);
            Route::put('/{coupon}', [\App\Http\Controllers\Admin\CouponController::class, 'update']);
            Route::delete('/{coupon}', [\App\Http\Controllers\Admin\CouponController::class, 'destroy']);
            Route::patch('/{coupon}/toggle', [\App\Http\Controllers\Admin\CouponController::class, 'toggleStatus']);
            Route::get('/{coupon}/statistics', [\App\Http\Controllers\Admin\CouponController::class, 'statistics']);
        });

        // Ratings & Reviews Management
        Route::prefix('ratings')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\RatingController::class, 'index']);
            Route::get('/statistics', [\App\Http\Controllers\Admin\RatingController::class, 'statistics']);
            Route::get('/partner', [\App\Http\Controllers\Admin\RatingController::class, 'partnerRatings']);
            Route::get('/customer', [\App\Http\Controllers\Admin\RatingController::class, 'customerRatings']);
            Route::get('/top-partners', [\App\Http\Controllers\Admin\RatingController::class, 'topRatedPartners']);
            Route::get('/{rating}', [\App\Http\Controllers\Admin\RatingController::class, 'show']);
            Route::delete('/{rating}', [\App\Http\Controllers\Admin\RatingController::class, 'destroy']);
        });
    });
});
