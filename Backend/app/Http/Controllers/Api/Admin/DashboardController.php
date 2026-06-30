<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\CarStatus;
use App\Enums\PaymentStatus;
use App\Enums\ReservationStatus;
use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Client;
use App\Models\Extra;
use App\Models\PickupLocation;
use App\Models\Reservation;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        return response()->json([
            'cars' => [
                'total' => Car::query()->count(),
                'active' => Car::query()->where('is_active', true)->count(),
                'available' => Car::query()->where('status', CarStatus::Available->value)->count(),
                'rented' => Car::query()->where('status', CarStatus::Rented->value)->count(),
                'maintenance' => Car::query()->where('status', CarStatus::Maintenance->value)->count(),
            ],
            'reservations' => [
                'total' => Reservation::query()->count(),
                'pending' => Reservation::query()->where('status', ReservationStatus::Pending->value)->count(),
                'confirmed' => Reservation::query()->where('status', ReservationStatus::Confirmed->value)->count(),
                'ongoing' => Reservation::query()->where('status', ReservationStatus::Ongoing->value)->count(),
                'completed' => Reservation::query()->where('status', ReservationStatus::Completed->value)->count(),
                'cancelled' => Reservation::query()->where('status', ReservationStatus::Cancelled->value)->count(),
            ],
            'clients' => Client::query()->count(),
            'locations' => PickupLocation::query()->where('is_active', true)->count(),
            'extras' => Extra::query()->where('is_active', true)->count(),
            'testimonials' => [
                'total' => Testimonial::query()->count(),
                'visible' => Testimonial::query()->where('is_visible', true)->count(),
            ],
            'revenue' => [
                'paid_total' => (float) Reservation::query()
                    ->where('payment_status', PaymentStatus::Paid->value)
                    ->sum('total_price'),
                'deposits_collected' => (float) Reservation::query()
                    ->whereIn('payment_status', [
                        PaymentStatus::Deposit->value,
                        PaymentStatus::Paid->value,
                    ])
                    ->sum('deposit_amount'),
            ],
            'today' => [
                'pickups' => Reservation::query()->whereDate('pickup_date', today())->count(),
                'dropoffs' => Reservation::query()->whereDate('dropoff_date', today())->count(),
            ],
        ]);
    }
}
