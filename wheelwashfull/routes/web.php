<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\AuthController;
use App\Http\Controllers\Customer\HomeController;
use App\Http\Controllers\Customer\VehicleController;
use App\Http\Controllers\Customer\ServiceController;
use App\Http\Controllers\Customer\BookingController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\PaymentController;

Route::get('/', function () {
    return redirect()->route('customer.login');
});

// Partner Auth Routes (Guest)
Route::middleware('guest')->prefix('partner')->name('partner.')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Partner\AuthController::class, 'showLogin'])->name('login');
    Route::post('/send-otp', [\App\Http\Controllers\Partner\AuthController::class, 'sendOtp'])->name('send-otp');
    Route::post('/verify-otp', [\App\Http\Controllers\Partner\AuthController::class, 'verifyOtp'])->name('verify-otp');
});

// Partner Protected Routes
Route::middleware(['auth', 'role:partner'])->prefix('partner')->name('partner.')->group(function () {
    Route::post('/logout', [\App\Http\Controllers\Partner\AuthController::class, 'logout'])->name('logout');

    Route::get('/jobs/today', [\App\Http\Controllers\Partner\JobController::class, 'today'])->name('jobs.today');
    Route::get('/jobs/upcoming', [\App\Http\Controllers\Partner\JobController::class, 'upcoming'])->name('jobs.upcoming');
    Route::get('/jobs/{booking}', [\App\Http\Controllers\Partner\JobController::class, 'show'])->name('jobs.show');
    Route::post('/jobs/{booking}/accept', [\App\Http\Controllers\Partner\JobController::class, 'accept'])->name('jobs.accept');
    Route::post('/jobs/{booking}/on-the-way', [\App\Http\Controllers\Partner\JobController::class, 'onTheWay'])->name('jobs.on-the-way');
    Route::post('/jobs/{booking}/start', [\App\Http\Controllers\Partner\JobController::class, 'start'])->name('jobs.start');
    Route::post('/jobs/{booking}/complete', [\App\Http\Controllers\Partner\JobController::class, 'complete'])->name('jobs.complete');
    Route::post('/jobs/{booking}/collect-cod', [\App\Http\Controllers\Partner\JobController::class, 'collectCod'])->name('jobs.collect-cod');
    Route::post('/jobs/{booking}/upload-image', [\App\Http\Controllers\Partner\JobController::class, 'uploadImage'])->name('jobs.upload-image');

    Route::get('/earnings', [\App\Http\Controllers\Partner\EarningsController::class, 'index'])->name('earnings.index');

    Route::get('/profile', [\App\Http\Controllers\Partner\ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile', [\App\Http\Controllers\Partner\ProfileController::class, 'update'])->name('profile.update');

    Route::get('/reviews', [\App\Http\Controllers\Partner\ReviewController::class, 'index'])->name('reviews.index');
});

// Admin Auth Routes (Guest)
Route::middleware('guest')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Admin\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Admin\AuthController::class, 'login']);
});

// Admin Protected Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('/logout', [\App\Http\Controllers\Admin\AuthController::class, 'logout'])->name('logout');
    
    // Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('super_admin')->group(function () {
        Route::resource('cities', \App\Http\Controllers\Admin\ServiceCityController::class)->except(['create', 'edit', 'show']);
        Route::resource('zones', \App\Http\Controllers\Admin\ServiceZoneController::class)->except(['create', 'edit', 'show']);
        Route::resource('city-admins', \App\Http\Controllers\Admin\CityAdminController::class)
            ->parameters(['city-admins' => 'cityAdmin'])
            ->except(['create', 'edit', 'show']);
    });
    
    // Customers
    Route::get('/customers', [\App\Http\Controllers\Admin\CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{customer}', [\App\Http\Controllers\Admin\CustomerController::class, 'show'])->name('customers.show');
    
    // Partners
    Route::get('/partners', [\App\Http\Controllers\Admin\PartnerController::class, 'index'])->name('partners.index');
    Route::get('/partners/create', [\App\Http\Controllers\Admin\PartnerController::class, 'create'])->name('partners.create');
    Route::post('/partners', [\App\Http\Controllers\Admin\PartnerController::class, 'store'])->name('partners.store');
    Route::get('/partners/{partner}/edit', [\App\Http\Controllers\Admin\PartnerController::class, 'edit'])->name('partners.edit');
    Route::put('/partners/{partner}', [\App\Http\Controllers\Admin\PartnerController::class, 'update'])->name('partners.update');
    Route::patch('/partners/{partner}/toggle-status', [\App\Http\Controllers\Admin\PartnerController::class, 'toggleStatus'])->name('partners.toggleStatus');
    Route::get('/partners/{partner}', [\App\Http\Controllers\Admin\PartnerController::class, 'show'])->name('partners.show');
    
    // Services
    Route::resource('services', \App\Http\Controllers\Admin\ServiceController::class);
    Route::resource('service-categories', \App\Http\Controllers\Admin\ServiceCategoryController::class);

    // Banners
    Route::resource('banners', \App\Http\Controllers\Admin\BannerController::class)->except(['show']);
    Route::patch('/banners/{banner}/toggle', [\App\Http\Controllers\Admin\BannerController::class, 'toggle'])->name('banners.toggle');
    Route::redirect('/app-banners', '/admin/banners');
    
    // Slots
    Route::resource('slots', \App\Http\Controllers\Admin\SlotController::class);
    
    // Bookings
    Route::get('/bookings', [\App\Http\Controllers\Admin\BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{booking}', [\App\Http\Controllers\Admin\BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/assign-team', [\App\Http\Controllers\Admin\BookingController::class, 'assignTeam'])->name('bookings.assignTeam');
    Route::post('/bookings/{booking}/update-status', [\App\Http\Controllers\Admin\BookingController::class, 'updateStatus'])->name('bookings.updateStatus');
    Route::post('/bookings/{booking}/update-payment-status', [\App\Http\Controllers\Admin\BookingController::class, 'updatePaymentStatus'])->name('bookings.updatePaymentStatus');
    Route::get('/assign-team', [\App\Http\Controllers\Admin\AdminPageController::class, 'assignTeam'])->name('assign-team.index');
    Route::post('/assign-team/{booking}', [\App\Http\Controllers\Admin\AdminPageController::class, 'assignTeamStore'])->name('assign-team.store');
    Route::get('/team-management', [\App\Http\Controllers\Admin\AdminPageController::class, 'teamManagement'])->name('team-management.index');
    Route::prefix('team/{type}')->name('team.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\TeamController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\TeamController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\TeamController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [\App\Http\Controllers\Admin\TeamController::class, 'edit'])->name('edit');
        Route::put('/{user}', [\App\Http\Controllers\Admin\TeamController::class, 'update'])->name('update');
        Route::patch('/{user}/toggle', [\App\Http\Controllers\Admin\TeamController::class, 'toggle'])->name('toggle');
    });
    Route::get('/subscriptions', [\App\Http\Controllers\Admin\AdminPageController::class, 'subscriptions'])->name('subscriptions.index');
    Route::resource('subscription-plans', \App\Http\Controllers\Admin\SubscriptionPlanController::class)->except(['show', 'destroy']);
    Route::patch('/subscription-plans/{subscriptionPlan}/toggle', [\App\Http\Controllers\Admin\SubscriptionPlanController::class, 'toggle'])->name('subscription-plans.toggle');
    Route::get('/earnings', [\App\Http\Controllers\Admin\AdminPageController::class, 'earnings'])->name('earnings.index');
    Route::get('/settings', [\App\Http\Controllers\Admin\AdminPageController::class, 'settings'])->name('settings.index');
    
    // Payments
    Route::get('/payments', [\App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('payments.index');
    
    // Coupons
    Route::resource('coupons', \App\Http\Controllers\Admin\CouponWebController::class);
    
    // Reviews
    Route::get('/reviews', [\App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('reviews.index');
    Route::get('/reviews/{review}', [\App\Http\Controllers\Admin\ReviewController::class, 'show'])->name('reviews.show');
    Route::delete('/reviews/{review}', [\App\Http\Controllers\Admin\ReviewController::class, 'destroy'])->name('reviews.destroy');
    
    // Reports
    Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');

    // Notifications
    Route::resource('notifications', \App\Http\Controllers\Admin\NotificationController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::post('/notifications/{notification}/send', [\App\Http\Controllers\Admin\NotificationController::class, 'send'])->name('notifications.send');
});

// Customer Auth Routes (Guest)
Route::middleware('guest')->prefix('customer')->name('customer.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/send-otp', [AuthController::class, 'sendOtp'])->name('send-otp');
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->name('verify-otp');
});

// Customer Protected Routes
Route::middleware(['auth', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Profile
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Vehicles
    Route::resource('vehicles', VehicleController::class);

    // Services
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/{service}', [ServiceController::class, 'show'])->name('services.show');

    // Bookings
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create/{service}', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings/select-slot', [BookingController::class, 'selectSlot'])->name('bookings.select-slot');
    Route::get('/bookings/slots-ajax', [BookingController::class, 'getSlotsAjax'])->name('bookings.slots.ajax');
    Route::post('/bookings/apply-coupon', [BookingController::class, 'applyCoupon'])->name('bookings.apply-coupon');
    Route::post('/bookings/validate-coupon', [BookingController::class, 'validateCoupon'])->name('bookings.validate-coupon');
    Route::post('/bookings/select-payment', [BookingController::class, 'selectPayment'])->name('bookings.select-payment');
    Route::post('/bookings/store', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}/confirmation', [BookingController::class, 'confirmation'])->name('bookings.confirmation');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{booking}/upload-image', [BookingController::class, 'uploadImage'])->name('bookings.upload-image');
    Route::get('/bookings/{booking}/rate', [BookingController::class, 'rate'])->name('bookings.rate');
    Route::post('/bookings/{booking}/rate', [BookingController::class, 'storeRating'])->name('bookings.store-rating');

    // Payments
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/{payment}/checkout', [PaymentController::class, 'checkout'])->name('checkout');
        Route::match(['get', 'post'], '/{payment}/success', [PaymentController::class, 'success'])->name('success');
        Route::match(['get', 'post'], '/{payment}/failed', [PaymentController::class, 'failed'])->name('failed');
    });
});
