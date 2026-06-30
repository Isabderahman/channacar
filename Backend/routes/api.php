<?php

use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\CarController as AdminCarController;
use App\Http\Controllers\Api\Admin\CarImageController;
use App\Http\Controllers\Api\Admin\ClientController;
use App\Http\Controllers\Api\Admin\ContractController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\ExtraController;
use App\Http\Controllers\Api\Admin\LocationController;
use App\Http\Controllers\Api\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\Api\Admin\SeasonController;
use App\Http\Controllers\Api\Admin\TestimonialController as AdminTestimonialController;
use App\Http\Controllers\Api\Public\CarController as PublicCarController;
use App\Http\Controllers\Api\Public\ReferenceController as PublicReferenceController;
use App\Http\Controllers\Api\Public\ReservationController as PublicReservationController;
use App\Http\Controllers\Api\Public\TestimonialController as PublicTestimonialController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [AuthController::class, 'login']);

Route::prefix('public')->group(function () {
    Route::get('/cars', [PublicCarController::class, 'index']);
    Route::get('/cars/{car}', [PublicCarController::class, 'show']);
    Route::get('/categories', [PublicReferenceController::class, 'categories']);
    Route::get('/locations', [PublicReferenceController::class, 'locations']);
    Route::get('/extras', [PublicReferenceController::class, 'extras']);
    Route::get('/testimonials', [PublicTestimonialController::class, 'index']);
    Route::post('/reservations', [PublicReservationController::class, 'store']);
    Route::post('/testimonials', [PublicTestimonialController::class, 'store']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('cars', AdminCarController::class);
    Route::post('/cars/{car}/images', [CarImageController::class, 'store']);
    Route::delete('/images/{image}', [CarImageController::class, 'destroy']);

    Route::apiResource('seasons', SeasonController::class);
    Route::apiResource('locations', LocationController::class);
    Route::apiResource('extras', ExtraController::class);
    Route::apiResource('clients', ClientController::class);
    Route::apiResource('reservations', AdminReservationController::class);
    Route::patch('/reservations/{reservation}/status', [AdminReservationController::class, 'updateStatus']);
    Route::patch('/reservations/{reservation}/contract', [ContractController::class, 'update']);
    Route::post('/reservations/{reservation}/documents', [ContractController::class, 'uploadDocuments']);
    Route::get('/reservations/{reservation}/contract', [ContractController::class, 'download']);
    Route::apiResource('testimonials', AdminTestimonialController::class);
    Route::patch('/testimonials/{testimonial}/visibility', [AdminTestimonialController::class, 'updateVisibility']);
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
});
