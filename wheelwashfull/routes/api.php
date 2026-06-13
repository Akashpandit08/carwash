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
    Route::post('/login', [AuthController::class, 'login']);
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
        Route::get('/services', [\App\Http\Controllers\Api\ServiceController::class, 'index']);
        Route::get('/services/{service}', [\App\Http\Controllers\Api\ServiceController::class, 'show']);
        Route::get('/subscription-plans', [\App\Http\Controllers\Api\Customer\SubscriptionController::class, 'plans']);
        Route::post('/subscriptions/purchase', [\App\Http\Controllers\Api\Customer\SubscriptionController::class, 'purchase']);
        Route::get('/my-subscriptions', [\App\Http\Controllers\Api\Customer\SubscriptionController::class, 'mine']);
        Route::get('/my-subscriptions/{subscription}', [\App\Http\Controllers\Api\Customer\SubscriptionController::class, 'show']);
        Route::post('/subscriptions/{subscription}/book-wash', [\App\Http\Controllers\Api\Customer\SubscriptionController::class, 'bookWash']);
        Route::get('/home', [\App\Http\Controllers\Api\AppContentController::class, 'home']);
        Route::post('/device-token', [\App\Http\Controllers\Api\DeviceController::class, 'store']);
        Route::post('/location/update', [\App\Http\Controllers\Api\Operations\LocationController::class, 'update']);
        Route::post('/online-status', [\App\Http\Controllers\Api\Operations\LocationController::class, 'onlineStatus']);
        Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
        Route::post('/notifications/{id}/read', [\App\Http\Controllers\Api\NotificationController::class, 'markRead']);
        Route::post('/notifications/read-all', [\App\Http\Controllers\Api\NotificationController::class, 'readAll']);
    });

    Route::prefix('partner')->group(function () {
        Route::get('/banners', [\App\Http\Controllers\Api\AppContentController::class, 'banners']);
        Route::get('/notifications', [\App\Http\Controllers\Api\NotificationController::class, 'index']);
        Route::post('/device-token', [\App\Http\Controllers\Api\DeviceController::class, 'store']);
    });

    Route::middleware(['role:admin', 'city_scope'])->prefix('admin')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\Admin\DashboardController::class, 'index']);
        Route::get('/dashboard', [\App\Http\Controllers\Api\Admin\DashboardController::class, 'index']);
        Route::get('/reports', [\App\Http\Controllers\Api\Admin\ReportController::class, 'index']);
        Route::apiResource('subscription-plans', \App\Http\Controllers\Api\Admin\SubscriptionPlanController::class);
        Route::get('/subscriptions', [\App\Http\Controllers\Api\Admin\CustomerSubscriptionController::class, 'index']);
        Route::post('/subscriptions', [\App\Http\Controllers\Api\Admin\CustomerSubscriptionController::class, 'store']);
        Route::get('/subscriptions/{subscription}', [\App\Http\Controllers\Api\Admin\CustomerSubscriptionController::class, 'show']);
        Route::put('/subscriptions/{subscription}', [\App\Http\Controllers\Api\Admin\CustomerSubscriptionController::class, 'update']);
        Route::post('/subscriptions/{subscription}/activate', [\App\Http\Controllers\Api\Admin\CustomerSubscriptionController::class, 'activate']);
        Route::post('/subscriptions/{subscription}/cancel', [\App\Http\Controllers\Api\Admin\CustomerSubscriptionController::class, 'cancel']);
        Route::post('/subscriptions/{subscription}/mark-paid', [\App\Http\Controllers\Api\Admin\CustomerSubscriptionController::class, 'markPaid']);

        Route::middleware('super_admin')->group(function () {
            Route::apiResource('cities', \App\Http\Controllers\Api\Admin\ServiceCityController::class);
            Route::apiResource('zones', \App\Http\Controllers\Api\Admin\ServiceZoneController::class);
            Route::get('/city-admins', [\App\Http\Controllers\Api\Admin\CityAdminController::class, 'index']);
            Route::post('/city-admins', [\App\Http\Controllers\Api\Admin\CityAdminController::class, 'store']);
            Route::get('/city-admins/{id}', [\App\Http\Controllers\Api\Admin\CityAdminController::class, 'show']);
            Route::put('/city-admins/{id}', [\App\Http\Controllers\Api\Admin\CityAdminController::class, 'update']);
            Route::delete('/city-admins/{id}', [\App\Http\Controllers\Api\Admin\CityAdminController::class, 'destroy']);
        });

        Route::get('/bookings', [\App\Http\Controllers\Api\Admin\BookingController::class, 'index']);
        Route::get('/bookings/{booking}', [\App\Http\Controllers\Api\Admin\BookingController::class, 'show']);
        Route::post('/bookings/{booking}/assign-worker', [\App\Http\Controllers\Api\Admin\BookingController::class, 'assignWorker']);
        Route::post('/bookings/{booking}/assign-partner', [\App\Http\Controllers\Api\Admin\BookingController::class, 'assignPartner']);
        Route::post('/bookings/{booking}/assign-team', [\App\Http\Controllers\Api\Admin\BookingController::class, 'assignTeam']);
        Route::post('/bookings/{booking}/assign-pickup-driver', [\App\Http\Controllers\Api\Admin\BookingController::class, 'assignPickupDriver']);
        Route::post('/bookings/{booking}/status', [\App\Http\Controllers\Api\Admin\BookingController::class, 'updateStatus']);
        Route::get('/bookings/{booking}/status-logs', [\App\Http\Controllers\Api\Admin\BookingController::class, 'statusLogs']);
        Route::get('/bookings/{booking}/media', [\App\Http\Controllers\Api\Admin\BookingController::class, 'media']);
        Route::get('/bookings/{booking}/tracking', [\App\Http\Controllers\Api\Admin\BookingController::class, 'tracking']);

        Route::get('/workers', [\App\Http\Controllers\Api\Admin\WorkerController::class, 'index']);
        Route::post('/workers', [\App\Http\Controllers\Api\Admin\WorkerController::class, 'store']);
        Route::put('/workers/{worker}', [\App\Http\Controllers\Api\Admin\WorkerController::class, 'update']);
        Route::get('/workers/{worker}', [\App\Http\Controllers\Api\Admin\WorkerController::class, 'show']);
        Route::patch('/workers/{worker}/toggle-status', [\App\Http\Controllers\Api\Admin\WorkerController::class, 'toggleStatus']);

        Route::get('/pickup-drivers', [\App\Http\Controllers\Api\Admin\PickupDriverController::class, 'index']);
        Route::post('/pickup-drivers', [\App\Http\Controllers\Api\Admin\PickupDriverController::class, 'store']);
        Route::put('/pickup-drivers/{driver}', [\App\Http\Controllers\Api\Admin\PickupDriverController::class, 'update']);
        Route::get('/pickup-drivers/{driver}', [\App\Http\Controllers\Api\Admin\PickupDriverController::class, 'show']);
        Route::patch('/pickup-drivers/{driver}/toggle-status', [\App\Http\Controllers\Api\Admin\PickupDriverController::class, 'toggleStatus']);

        Route::get('/partners', [\App\Http\Controllers\Api\Admin\PartnerController::class, 'index']);
        Route::post('/partners', [\App\Http\Controllers\Api\Admin\PartnerController::class, 'store']);
        Route::put('/partners/{partner}', [\App\Http\Controllers\Api\Admin\PartnerController::class, 'update']);
        Route::get('/partners/{partner}', [\App\Http\Controllers\Api\Admin\PartnerController::class, 'show']);
        Route::patch('/partners/{partner}/toggle-status', [\App\Http\Controllers\Api\Admin\PartnerController::class, 'toggleStatus']);

        Route::get('/payouts', [\App\Http\Controllers\Api\Admin\PayoutController::class, 'index']);
        Route::post('/payouts/{payout}/approve', [\App\Http\Controllers\Api\Admin\PayoutController::class, 'approve']);
        Route::post('/payouts/{payout}/reject', [\App\Http\Controllers\Api\Admin\PayoutController::class, 'reject']);
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

    Route::middleware('role:worker')->prefix('worker')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Api\Operations\Worker\JobController::class, 'dashboard']);
        Route::get('/earnings', [\App\Http\Controllers\Api\Operations\Worker\JobController::class, 'earnings']);
        Route::get('/profile', [\App\Http\Controllers\Api\Operations\Worker\JobController::class, 'profile']);
        Route::put('/profile', [\App\Http\Controllers\Api\Operations\Worker\JobController::class, 'updateProfile']);
        Route::get('/jobs', [\App\Http\Controllers\Api\Operations\Worker\JobController::class, 'index']);
        Route::get('/jobs/{booking}', [\App\Http\Controllers\Api\Operations\Worker\JobController::class, 'show']);
        Route::post('/jobs/{booking}/status', [\App\Http\Controllers\Api\Operations\Worker\JobController::class, 'status']);
        Route::post('/jobs/{booking}/media', [\App\Http\Controllers\Api\Operations\Worker\JobController::class, 'media']);
        Route::post('/jobs/{booking}/start-travel', [\App\Http\Controllers\Api\Operations\Worker\JobController::class, 'startTravel']);
        Route::post('/jobs/{booking}/arrived', [\App\Http\Controllers\Api\Operations\Worker\JobController::class, 'arrived']);
        Route::post('/jobs/{booking}/start-service', [\App\Http\Controllers\Api\Operations\Worker\JobController::class, 'startService']);
        Route::post('/jobs/{booking}/complete-service', [\App\Http\Controllers\Api\Operations\Worker\JobController::class, 'completeService']);
        Route::post('/jobs/{booking}/collect-cash-complete', [\App\Http\Controllers\Api\Operations\Worker\JobController::class, 'collectCashComplete']);
        Route::post('/jobs/{booking}/complete', [\App\Http\Controllers\Api\Operations\Worker\JobController::class, 'complete']);
    });

    Route::middleware('role:pickup_driver')->prefix('pickup-driver')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Api\Operations\Driver\JobController::class, 'dashboard']);
        Route::get('/earnings', [\App\Http\Controllers\Api\Operations\Driver\JobController::class, 'earnings']);
        Route::get('/profile', [\App\Http\Controllers\Api\Operations\Driver\JobController::class, 'profile']);
        Route::put('/profile', [\App\Http\Controllers\Api\Operations\Driver\JobController::class, 'updateProfile']);
        Route::get('/jobs', [\App\Http\Controllers\Api\Operations\Driver\JobController::class, 'index']);
        Route::get('/jobs/{booking}', [\App\Http\Controllers\Api\Operations\Driver\JobController::class, 'show']);
        Route::post('/jobs/{booking}/status', [\App\Http\Controllers\Api\Operations\Driver\JobController::class, 'status']);
        Route::post('/jobs/{booking}/media', [\App\Http\Controllers\Api\Operations\Driver\JobController::class, 'media']);
        Route::post('/jobs/{booking}/start-pickup-travel', [\App\Http\Controllers\Api\Operations\Driver\JobController::class, 'startPickupTravel']);
        Route::post('/jobs/{booking}/arrived-customer', [\App\Http\Controllers\Api\Operations\Driver\JobController::class, 'arrivedCustomer']);
        Route::post('/jobs/{booking}/pickup-vehicle', [\App\Http\Controllers\Api\Operations\Driver\JobController::class, 'pickupVehicle']);
        Route::post('/jobs/{booking}/arrived-partner', [\App\Http\Controllers\Api\Operations\Driver\JobController::class, 'arrivedPartner']);
        Route::post('/jobs/{booking}/start-delivery', [\App\Http\Controllers\Api\Operations\Driver\JobController::class, 'startDelivery']);
        Route::post('/jobs/{booking}/arrived-delivery', [\App\Http\Controllers\Api\Operations\Driver\JobController::class, 'arrivedDelivery']);
        Route::post('/jobs/{booking}/deliver-vehicle', [\App\Http\Controllers\Api\Operations\Driver\JobController::class, 'deliverVehicle']);
        Route::post('/jobs/{booking}/collect-cash-complete', [\App\Http\Controllers\Api\Operations\Driver\JobController::class, 'collectCashComplete']);
        Route::post('/jobs/{booking}/complete', [\App\Http\Controllers\Api\Operations\Driver\JobController::class, 'complete']);
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
        // Dashboard & Earnings
        Route::get('/dashboard', [\App\Http\Controllers\Api\Partner\DashboardController::class, 'index']);
        Route::get('/earnings', [\App\Http\Controllers\Api\Partner\EarningsController::class, 'index']);
        Route::get('/ledger', [\App\Http\Controllers\Api\Partner\EarningsController::class, 'ledger']);
        Route::post('/payout-request', [\App\Http\Controllers\Api\Partner\EarningsController::class, 'requestPayout']);
        
        // Staff Management
        Route::apiResource('workers', \App\Http\Controllers\Api\Partner\WorkerController::class);
        Route::get('/workers/{id}/location', [\App\Http\Controllers\Api\Partner\WorkerController::class, 'location']);
        
        Route::apiResource('drivers', \App\Http\Controllers\Api\Partner\DriverController::class);
        Route::get('/drivers/{id}/location', [\App\Http\Controllers\Api\Partner\DriverController::class, 'location']);

        // Bookings/Jobs
        Route::get('/jobs', [\App\Http\Controllers\Api\Partner\BookingController::class, 'index']);
        Route::get('/jobs/{booking}', [\App\Http\Controllers\Api\Partner\BookingController::class, 'show']);
        Route::get('/jobs/{booking}/tracking', [\App\Http\Controllers\Api\Partner\BookingController::class, 'tracking']);
        Route::post('/jobs/{booking}/accept', [\App\Http\Controllers\Api\Partner\BookingController::class, 'accept']);
        Route::post('/jobs/{booking}/assign-worker', [\App\Http\Controllers\Api\Partner\BookingController::class, 'assignWorker']);
        Route::post('/jobs/{booking}/assign-driver', [\App\Http\Controllers\Api\Partner\BookingController::class, 'assignDriver']);
        Route::post('/jobs/{booking}/accept-vehicle', [\App\Http\Controllers\Api\Partner\BookingController::class, 'acceptVehicle']);
        Route::post('/jobs/{booking}/start-service', [\App\Http\Controllers\Api\Partner\BookingController::class, 'startService']);
        Route::post('/jobs/{booking}/complete-service', [\App\Http\Controllers\Api\Partner\BookingController::class, 'completeService']);
        Route::post('/jobs/{booking}/handover-to-driver', [\App\Http\Controllers\Api\Partner\BookingController::class, 'handoverToDriver']);
        Route::post('/jobs/{booking}/mark-customer-arrived', [\App\Http\Controllers\Api\Partner\BookingController::class, 'markCustomerArrived']);
        Route::post('/jobs/{booking}/collect-cash', [\App\Http\Controllers\Api\Partner\BookingController::class, 'collectCash']);

        // Ratings & Reviews
        Route::prefix('ratings')->group(function () {
            Route::get('/', [\App\Http\Controllers\Partner\RatingController::class, 'index']);
            Route::get('/statistics', [\App\Http\Controllers\Partner\RatingController::class, 'statistics']);
            Route::get('/recent', [\App\Http\Controllers\Partner\RatingController::class, 'recent']);
        });
    });

    // Admin API endpoints (Continued from above)
    Route::middleware(['role:admin', 'city_scope'])->prefix('admin')->group(function () {
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
