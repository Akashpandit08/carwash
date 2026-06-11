<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingImage;
use App\Models\Coupon;
use App\Models\Rating;
use App\Models\Service;
use App\Models\Vehicle;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function create(Service $service)
    {
        if (!$service->is_active) {
            abort(404);
        }

        $vehicles = Vehicle::where('user_id', auth()->id())->get();

        if ($vehicles->isEmpty()) {
            return redirect()->route('customer.vehicles.create')
                ->with('info', 'Please add a vehicle first to book a service');
        }

        return view('customer.bookings.create', compact('service', 'vehicles'));
    }

    public function selectSlot(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'vehicle_id' => 'required|exists:vehicles,id,user_id,' . auth()->id(),
            'address' => 'required|string',
        ]);

        $service = Service::where('id', $request->service_id)->where('is_active', true)->firstOrFail();
        $vehicle = Vehicle::where('id', $request->vehicle_id)->where('user_id', auth()->id())->firstOrFail();

        session()->put('booking_data', [
            'service_id' => $service->id,
            'vehicle_id' => $vehicle->id,
            'address' => $request->address,
        ]);

        return view('customer.bookings.slot', compact('service', 'vehicle'));
    }

    public function getSlotsAjax(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'service_id' => 'required|exists:services,id',
        ]);

        $availableSlots = app(\App\Services\SlotService::class)->getAvailableSlots($request->date, $request->service_id);
        
        return response()->json([
            'success' => true,
            'slots' => $availableSlots
        ]);
    }

    public function applyCoupon(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'vehicle_id' => 'required|exists:vehicles,id,user_id,' . auth()->id(),
            'booking_date' => 'required|date',
            'slot_time' => 'required',
            'address' => 'required|string',
        ]);

        $service = Service::where('id', $request->service_id)->where('is_active', true)->firstOrFail();
        $vehicle = Vehicle::where('id', $request->vehicle_id)->where('user_id', auth()->id())->firstOrFail();
        $coupons = Coupon::where('is_active', true)->get();

        session()->put('booking_data', array_merge($validated, [
            'price' => $service->price,
        ]));

        return view('customer.bookings.coupon', compact('service', 'vehicle', 'coupons'));
    }

    public function validateCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string',
        ]);

        $bookingData = session()->get('booking_data');
        if (!$bookingData) {
            return response()->json(['success' => false, 'message' => 'Session expired.']);
        }

        $pricing = $this->bookingService->calculatePricing($bookingData['service_id'], $request->coupon_code);

        if (!$pricing['success']) {
            return response()->json([
                'success' => false,
                'message' => $pricing['message'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $pricing['message'],
            'discount' => $pricing['discount'],
            'final_price' => $pricing['final_price'],
            'coupon_id' => $pricing['coupon_id'],
        ]);
    }

    public function selectPayment(Request $request)
    {
        $bookingData = session()->get('booking_data');

        if (!$bookingData) {
            return redirect()->route('customer.services.index');
        }

        $service = Service::findOrFail($bookingData['service_id']);
        
        $couponId = $request->input('coupon_id', null);
        $couponCode = null;
        if ($couponId) {
            $coupon = Coupon::find($couponId);
            $couponCode = $coupon ? $coupon->code : null;
        }

        $pricing = $this->bookingService->calculatePricing($service->id, $couponCode);
        $finalPrice = $pricing['final_price'];

        session()->put('booking_data', array_merge($bookingData, [
            'discount' => $pricing['discount'],
            'coupon_id' => $pricing['coupon_id'],
            'final_price' => $finalPrice,
            'coupon_code' => $couponCode,
        ]));

        $isRazorpayConfigured = !empty(config('services.razorpay.key'));

        return view('customer.bookings.payment', compact('service', 'finalPrice', 'isRazorpayConfigured'));
    }

    public function store(Request $request)
    {
        $isRazorpayConfigured = !empty(config('services.razorpay.key'));
        $allowedMethods = $isRazorpayConfigured ? 'cod,online' : 'cod';

        $validated = $request->validate([
            'payment_method' => 'required|in:' . $allowedMethods,
        ]);

        $bookingData = session()->get('booking_data');

        if (!$bookingData) {
            return redirect()->route('customer.services.index');
        }

        $bookingData['payment_method'] = $validated['payment_method'];
        $bookingData['user_id'] = auth()->id();

        // Prevent overbooking
        $availableSlots = $this->bookingService->generateTimeSlots($bookingData['booking_date'], $bookingData['service_id']);
        if (!in_array($bookingData['slot_time'], $availableSlots)) {
            return redirect()->route('customer.services.show', $bookingData['service_id'])->with('error', 'The selected slot is no longer available. Please book again.');
        }

        $booking = $this->bookingService->createBooking($bookingData);

        session()->forget('booking_data');

        if ($booking->payment_method === 'online' && $booking->latestPayment) {
            return redirect()->route('customer.payments.checkout', $booking->latestPayment);
        }

        return redirect()->route('customer.bookings.confirmation', $booking->id);
    }

    public function confirmation(Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        $booking->load(['service', 'vehicle', 'latestPayment']);

        return view('customer.bookings.confirmation', compact('booking'));
    }

    public function index(Request $request)
    {
        $query = Booking::with(['service', 'vehicle', 'rating'])
            ->where('user_id', auth()->id())
            ->latest();

        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $bookings = $query->paginate(10);

        return view('customer.bookings.index', compact('bookings'));
    }

    public function show(Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        $booking->load(['service', 'vehicle', 'images', 'rating', 'latestPayment']);

        return view('customer.bookings.show', compact('booking'));
    }

    public function uploadImage(Request $request, Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'image_type' => 'required|in:before,after',
        ]);

        $path = $request->file('image')->storeOnCloudinary('booking_images')->getSecurePath();

        BookingImage::create([
            'booking_id' => $booking->id,
            'image_type' => $request->image_type,
            'image_path' => $path,
            'uploaded_by' => auth()->id(),
        ]);

        return back()->with('success', 'Image uploaded successfully');
    }

    public function rate(Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        if ($booking->status !== 'completed') {
            return redirect()->route('customer.bookings.show', $booking)->with('error', 'You can only rate completed bookings');
        }

        if ($booking->rating) {
            return redirect()->route('customer.bookings.show', $booking)->with('info', 'You have already rated this booking');
        }

        $booking->load(['service', 'vehicle']);

        return view('customer.bookings.rate', compact('booking'));
    }

    public function storeRating(Request $request, Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        if ($booking->rating) {
            return redirect()->route('customer.bookings.show', $booking)->with('info', 'You have already rated this booking');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ]);

        Rating::create([
            'booking_id' => $booking->id,
            'user_id'    => auth()->id(),
            'partner_id' => $booking->partner_id,
            'rating'     => $validated['rating'],
            'review'     => $validated['review'] ?? null,
        ]);

        return redirect()->route('customer.bookings.show', $booking)->with('success', 'Thank you for your rating!');
    }
}
